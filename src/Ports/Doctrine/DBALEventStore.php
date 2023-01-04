<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Types;
use RuntimeException;
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

    abstract protected function tableName(): string;

    /**
     * @param DomainEvent[] $events
     * @return AggregateRoot
     */
    abstract protected function createAggregateFromStream(array $events): AggregateRoot;

    abstract protected function handleNoEventFound(string $id): void;

    protected function getAggregateWithId(string $id): AggregateRoot
    {
        $this->ensureTableExists();

        $qb = $this->connection->createQueryBuilder();
        $expr = $qb->expr();
        $result = $qb
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
            ->execute();

        if (! $result instanceof Result) {
            throw new RuntimeException('An error occurred while executing statement.');
        }

        $stream = $result->fetchAll();

        if (count($stream) === 0) {
            $this->handleNoEventFound($id);
        }

        $aggregate = $this->createAggregateFromStream(
            array_map(
                /**
                 * @param array{"event_name":string, "payload":string} $eventRow
                 */
                function (array $eventRow): DomainEvent { // @phpstan-ignore-line
                    return $this->serializer->createEvent(
                        $eventRow['event_name'],
                        unserialize($eventRow['payload']) // @phpstan-ignore-line
                    );
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
        $result = $versionQb
            ->select(sprintf('COUNT(%s)', self::COLUMN_AGGREGATE_ID))
            ->from($this->tableName(), 'alias')
            ->andWhere($expr->eq('alias.' . self::COLUMN_AGGREGATE_ID, ':aggregate_id'))
            ->setParameter('aggregate_id', $id)
            ->execute();

        if (!$result instanceof Result) {
            throw new RuntimeException('An error occurred while executing statement.');
        }

        $version = (int) $result->fetchFirstColumn()[0]; // @phpstan-ignore-line
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

    /**
     * @param string $id
     * @param int $version
     * @param string $eventName
     * @param string[]|int[]|bool[]|float[] $payload
     */
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
                ]
            );
            $table->addColumn(
                self::COLUMN_EVENT_NAME,
                Types::STRING
            );
            $table->addColumn(
                self::COLUMN_PAYLOAD,
                Types::ARRAY
            );
            $table->addColumn(
                self::COLUMN_PUSHED_ON,
                Types::DATETIME_IMMUTABLE
            );
            $table->addColumn(
                self::COLUMN_VERSION,
                Types::BIGINT
            );

            $sqlStrings = $originalSchema->getMigrateToSql($newSchema, $this->connection->getDatabasePlatform());
            foreach ($sqlStrings as $sql) {
                $this->connection->exec($sql);
            }
        }
    }
}
