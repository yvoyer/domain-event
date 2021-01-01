<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Star\Component\DomainEvent\AggregateRoot;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Serialization\PayloadSerializer;
use function array_map;
use function count;
use function sprintf;
use function unserialize;

abstract class DBALEventStore
{
    protected const COLUMN_AGGREGATE_ID = 'aggregate_id';
    protected const COLUMN_PAYLOAD = 'payload';
    protected const COLUMN_EVENT_NAME = 'event_name';
    protected const COLUMN_PUSHED_ON = 'pushed_on';
    protected const COLUMN_VERSION = 'version';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var EventPublisher
     */
    private $publisher;

    /**
     * @var PayloadSerializer
     */
    private $serializer;

    public function __construct(
        Connection $connection,
        EventPublisher $publisher,
        PayloadSerializer $serializer
    ) {
        $this->connection = $connection;
        $this->publisher = $publisher;
        $this->serializer = $serializer;
    }

    protected abstract function tableName(): string;

    protected abstract function createAggregateFromStream(array $events): AggregateRoot;

    protected abstract function handleNoEventFound(string $id): void;

    protected function getAggregateWithId(string $id): AggregateRoot
    {
        $this->ensureTableExists();

        $qb = $this->connection->createQueryBuilder();
        $expr = $qb->expr();
        $stream = $qb
            ->select(
                [
                    'alias.' . self::COLUMN_AGGREGATE_ID,
                    'alias.' . self::COLUMN_EVENT_NAME,
                    'alias.' . self::COLUMN_PAYLOAD,
                ]
            )
            ->from($this->tableName(), 'alias')
            ->andWhere($expr->eq('alias.' . self::COLUMN_AGGREGATE_ID, ':aggregate_id'))
            ->addOrderBy('alias.' . self::COLUMN_VERSION, 'ASC')
            ->setParameter('aggregate_id', $id)
            ->execute()
            ->fetchAll();

        if (count($stream) === 0) {
            $this->handleNoEventFound($id);
        }

        $aggregate = $this->createAggregateFromStream(
            array_map(
                function (array $eventRow): DomainEvent {
                    return $this->serializer->createEvent($eventRow['event_name'], unserialize($eventRow['payload']));
                },
                $stream
            )
        );
        $aggregate->uncommitedEvents(); // reset on load

        return $aggregate;
    }

    protected function persistAggregate(string $id, AggregateRoot $aggregate): void
    {
        $this->ensureTableExists();

        $versionQb = $this->connection->createQueryBuilder();
        $expr = $versionQb->expr();
        $version = (int) $versionQb
            ->select(sprintf('count(%s)', self::COLUMN_AGGREGATE_ID))
            ->from($this->tableName(), 'alias')
            ->andWhere($expr->eq('alias.' . self::COLUMN_AGGREGATE_ID, ':aggregate_id'))
            ->setParameter('aggregate_id', $id)
            ->execute()
            ->fetchFirstColumn()[0];

        $events = $aggregate->uncommitedEvents();
        foreach ($events as $event) {
            $version++;
            $this->persistEvent(
                $id,
                $version,
                $this->serializer->createEventName($event),
                $this->serializer->createPayload($event)
            );

            $this->publisher->publish($event);
        }
    }

    private function persistEvent(string $id, int $version, string $eventName, array $payload): void
    {
        $this->connection->insert(
            $this->tableName(),
            [
                self::COLUMN_AGGREGATE_ID => $id,
                self::COLUMN_PAYLOAD => $payload,
                self::COLUMN_EVENT_NAME => $eventName,
                self::COLUMN_PUSHED_ON => new DateTimeImmutable(),
                self::COLUMN_VERSION => $version,
            ],
            [
                self::COLUMN_AGGREGATE_ID => Types::STRING,
                self::COLUMN_PAYLOAD => Types::ARRAY,
                self::COLUMN_EVENT_NAME => Types::STRING,
                self::COLUMN_PUSHED_ON => Types::DATETIME_IMMUTABLE,
                self::COLUMN_VERSION => Types::INTEGER,
            ]
        );
    }

    private function ensureTableExists(): void
    {
        $manager = $this->connection->getSchemaManager();
        if (!$manager->tablesExist([$this->tableName()])) {
            $originalSchema = $manager->createSchema();
            $newSchema = $manager->createSchema();

            $table = $newSchema->createTable($this->tableName());
            $table->addColumn(
                self::COLUMN_AGGREGATE_ID,
                Types::STRING,
                [
                    'length' => 50,
#                    'not_null' => false,
                ]
            );
            $table->addColumn(
                self::COLUMN_EVENT_NAME,
                Types::STRING,
                [
 #                   'not_null' => false,
                ]
            );
            $table->addColumn(
                self::COLUMN_PAYLOAD,
                Types::ARRAY,
                [
  #                  'not_null' => false,
                ]
            );
            $table->addColumn(
                self::COLUMN_PUSHED_ON,
                Types::DATETIME_IMMUTABLE,
                [
   #                 'not_null' => false,
                ]
            );
            $table->addColumn(
                self::COLUMN_VERSION,
                Types::BIGINT,
                [
    #                'not_null' => false,
                ]
            );

            $sqlStrings = $originalSchema->getMigrateToSql($newSchema, $this->connection->getDatabasePlatform());
            foreach ($sqlStrings as $sql) {
                $this->connection->exec($sql);
            }
        }
    }
}
