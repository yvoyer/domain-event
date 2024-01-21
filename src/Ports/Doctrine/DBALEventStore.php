<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Types;
use RuntimeException;
use Star\Component\DomainEvent\AggregateRoot;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Serialization\PayloadSerializer;
use function array_map;
use function count;
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

    /**
     * The columns to use int the fetch of the events. (Default = "version")
     *
     * @var string[]
     */
    private $orderColumns;

    /**
     * @param Connection $connection
     * @param EventPublisher $publisher
     * @param PayloadSerializer $serializer
     * @param string[] $orderColumns
     */
    public function __construct(
        Connection $connection,
        EventPublisher $publisher,
        PayloadSerializer $serializer,
        array $orderColumns = [
            self::COLUMN_VERSION,
        ]
    ) {
        $this->connection = $connection;
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->orderColumns = $orderColumns;
    }

    abstract protected function tableName(): string;

    /**
     * @param DomainEvent[] $events
     * @return AggregateRoot
     */
    abstract protected function createAggregateFromStream(array $events): AggregateRoot;

    abstract protected function handleNoEventFound(string $id): void;

    /**
     * The columns to use int the fetch of the events.
     *
     * @return string[]
     */
    protected function getOrderColumns(): array
    {
        return $this->orderColumns;
    }

    protected function getAggregateWithId(string $id): AggregateRoot
    {
        $this->ensureTableExists();

        $qb = $this->connection->createQueryBuilder();
        $expr = $qb->expr();
        $qb
            ->select(
                [
                    'alias.' . self::COLUMN_AGGREGATE_ID,
                    'alias.' . self::COLUMN_EVENT_NAME,
                    'alias.' . self::COLUMN_PAYLOAD,
                ]
            )
            ->from($this->tableName(), 'alias')
            ->andWhere($expr->eq('alias.' . self::COLUMN_AGGREGATE_ID, ':aggregate_id'))
            ->setParameter('aggregate_id', $id);
        foreach ($this->getOrderColumns() as $column) {
            $qb->addOrderBy($column, 'ASC');
        }

        $result = $qb->execute();
        if (! $result instanceof Result) {
            throw new RuntimeException('An error occurred while executing statement.');
        }
        $stream = $result->fetchAllAssociative();

        if (count($stream) === 0) {
            $this->handleNoEventFound($id);
        }

        /**
         * @param array{"event_name":string, "payload":string} $eventRow
         */
        $callback = function (array $eventRow): DomainEvent {
            return $this->serializer->createEvent(
                $eventRow['event_name'],
                unserialize($eventRow['payload']) // @phpstan-ignore-line
            );
        };

        $aggregate = $this->createAggregateFromStream(array_map($callback, $stream));
        $aggregate->uncommitedEvents(); // reset on load

        return $aggregate;
    }

    protected function persistAggregate(string $id, AggregateRoot $aggregate): void
    {
        $this->ensureTableExists();

        $events = $aggregate->uncommitedEvents();
        foreach ($events as $event) {
            $this->persistEvent(
                $id,
                $this->serializer->createEventName($event),
                $this->serializer->createPayload($event)
            );

            $this->publisher->publish($event);
        }
    }

    /**
     * This method allows to extract datetime value from payload, to use that date instead of a generated one.
     *
     * @param array<string, mixed> $payload
     * @return DateTimeImmutable
     */
    protected function createPushedOnDateFromPayload(array $payload): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    /**
     * @param string $id
     * @param string $eventName
     * @param array<string, string|int|bool|float> $payload
     *
     * @throws Exception
     */
    private function persistEvent(
        string $id,
        string $eventName,
        array $payload
    ): void {
        $expr = $this->connection->getExpressionBuilder();

        /**
         * Subquery hack to allow update of same table in same query for Mysql
         * @see https://stackoverflow.com/questions/45494/mysql-error-1093-cant-specify-target-table-for-update-in-from-clause
         */
        $subQuery = $this->connection->createQueryBuilder()
            ->select('COUNT(sub.version) + 1')
            ->from($this->tableName(), 'sub')
            ->where($expr->eq('sub.' . self::COLUMN_AGGREGATE_ID, ':aggregate_id'))
            ->getSQL();
        $this->connection->createQueryBuilder()
            ->insert($this->tableName())
            ->values(
                [
                    self::COLUMN_AGGREGATE_ID => ':aggregate_id',
                    self::COLUMN_PAYLOAD => ':payload',
                    self::COLUMN_EVENT_NAME => ':event',
                    self::COLUMN_PUSHED_ON => ':pushed_on',
                    self::COLUMN_VERSION => '(' . $subQuery . ')'
                ]
            )
            ->setParameters(
                [
                    'aggregate_id' => $id,
                    'payload' => $payload, // todo allow serialization in other format than array (JSON)
                    'event' => $eventName, // todo allow custom event_name (ie. "some_event_name")
                    'pushed_on' => $this->createPushedOnDateFromPayload($payload),
                ],
                [
                    self::COLUMN_AGGREGATE_ID => Types::STRING,
                    self::COLUMN_PAYLOAD => Types::ARRAY,
                    self::COLUMN_EVENT_NAME => Types::STRING,
                    self::COLUMN_PUSHED_ON => Types::DATETIME_IMMUTABLE,
                    self::COLUMN_VERSION => Types::INTEGER,
                ]
            )
            ->setParameter('aggregate_id', $id, Types::STRING)
            ->execute();
    }

    private function ensureTableExists(): void
    {
        // todo remove this automatic stuff
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
