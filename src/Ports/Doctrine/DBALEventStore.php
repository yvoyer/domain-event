<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Doctrine;

use Assert\Assertion;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Star\Component\DomainEvent\AggregateRoot;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Ports\Event\AfterEventPersist;
use Star\Component\DomainEvent\Ports\Event\BeforeEventPersist;
use Star\Component\DomainEvent\Serialization\Payload;
use Star\Component\DomainEvent\Serialization\PayloadSerializer;
use function array_map;
use function count;
use function json_decode;

abstract class DBALEventStore
{
    protected const COLUMN_AGGREGATE_ID = 'aggregate_id';
    protected const COLUMN_PAYLOAD = 'payload';
    protected const COLUMN_EVENT_NAME = 'event_name';
    protected const COLUMN_PUSHED_ON = 'pushed_on';
    protected const COLUMN_VERSION = 'version';

    /**
     * The columns to use int the fetch of the events. (Default = "version")
     *
     * @var array<int, string>
     */
    private array $orderColumns;

    /**
     * @param array<int, string> $orderColumns
     */
    public function __construct(
        protected Connection $connection,
        private EventPublisher $publisher,
        private PayloadSerializer $serializer,
        array $orderColumns = [
            self::COLUMN_VERSION,
        ]
    ) {
        $this->orderColumns = $orderColumns;
    }

    abstract protected function tableName(): string;

    /**
     * @param DomainEvent[] $events
     */
    abstract protected function createAggregateFromStream(array $events): AggregateRoot;

    abstract protected function handleNoEventFound(string $id): void;

    /**
     * The columns to use int the fetch of the events.
     *
     * @return array<int, string>
     */
    protected function getOrderColumns(): array
    {
        return $this->orderColumns;
    }

    protected function getAggregateWithId(string $id): AggregateRoot
    {
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

        /**
         * @var array<int, array{
         *    event_name: string,
         *    payload: string,
         * }> $stream
         */
        $stream = $qb
            ->executeQuery()
            ->fetchAllAssociative();

        if (count($stream) === 0) {
            $this->handleNoEventFound($id);
        }

        $callback = function (array $eventRow): DomainEvent {
            /**
             * @var array{
             *     event_name: class-string<DomainEvent>,
             *     payload: string,
             * } $eventRow
             */
            Assertion::keyExists($eventRow, self::COLUMN_EVENT_NAME);
            Assertion::keyExists($eventRow, self::COLUMN_PAYLOAD);

            return $this->serializer->createEvent(
                $eventRow[self::COLUMN_EVENT_NAME],
                Payload::fromArray($this->unserializePayloadColumn($eventRow[self::COLUMN_PAYLOAD]))
            );
        };

        $aggregate = $this->createAggregateFromStream(array_map($callback, $stream));
        $aggregate->uncommitedEvents(); // reset on load

        return $aggregate;
    }

    protected function persistAggregate(
        string $id,
        AggregateRoot $aggregate,
    ): void {
        $events = $aggregate->uncommitedEvents();
        foreach ($events as $event) {
            $this->persistEvent(
                $id,
                $this->serializer->createEventName($event),
                $this->serializer->createPayload($event),
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
    protected function createPushedOnDateFromPayload(
        array $payload,
    ): DateTimeImmutable {
        return new DateTimeImmutable();
    }

    /**
     * @param string $data
     * @return array<string, string|int|bool|float>
     * @see self::getPayloadType()
     */
    protected function unserializePayloadColumn(string $data): array
    {
        return json_decode($data, true); // @phpstan-ignore-line
    }

    /**
     * @return string
     * @see self::unserializeRowPayload() The unserialize strategy depends on the return type
     */
    protected function getPayloadType(): string
    {
        return Types::JSON;
    }

    protected function getPushedOnType(): string
    {
        return Types::DATETIME_IMMUTABLE;
    }

    private function persistEvent(
        string $id,
        string $eventName,
        Payload $payload,
    ): void {
        $expr = $this->connection->createExpressionBuilder();

        /**
         * Subquery hack to allow update of same table in same query for Mysql
         * @see https://stackoverflow.com/questions/45494/mysql-error-1093-cant-specify-target-table-for-update-in-from-clause
         */
        $subQuery = $this->connection->createQueryBuilder()
            ->select('COUNT(sub.' . self::COLUMN_VERSION . ') + 1')
            ->from($this->tableName(), 'sub')
            ->where($expr->eq('sub.' . self::COLUMN_AGGREGATE_ID, ':aggregate_id'))
            ->getSQL();

        $pushedOn = $this->createPushedOnDateFromPayload($payload->toArray());
        $this->beforeEventPersist(
            $id,
            $eventName,
            $payload,
            $builder = RowDatasetBuilder::fromPayload(
                [
                    self::COLUMN_AGGREGATE_ID => ':aggregate_id',
                    self::COLUMN_PAYLOAD => ':payload',
                    self::COLUMN_EVENT_NAME => ':event',
                    self::COLUMN_PUSHED_ON => ':pushed_on',
                    self::COLUMN_VERSION => '(' . $subQuery . ')'
                ],
                [
                    'aggregate_id' => $id,
                    'payload' => $payload->toArray(),
                    'event' => $eventName,
                    'pushed_on' => $pushedOn,
                ],
                [
                    self::COLUMN_AGGREGATE_ID => Types::STRING,
                    self::COLUMN_PAYLOAD => $this->getPayloadType(),
                    self::COLUMN_EVENT_NAME => Types::STRING,
                    self::COLUMN_PUSHED_ON => $this->getPushedOnType(),
                    self::COLUMN_VERSION => Types::BIGINT,
                ]
            ),
            $pushedOn
        );
        $this->publisher->publish(
            new BeforeEventPersist(
                $id,
                $eventName,
                $payload,
                $pushedOn
            )
        );

        $this->connection->createQueryBuilder()
            ->insert($this->tableName())
            ->values($builder->buildValues())
            ->setParameters(
                $builder->buildParameters(),
                $builder->buildTypes()
            )
            ->setParameter('aggregate_id', $id, Types::STRING)
            ->executeStatement();

        $this->afterEventPersist(
            $id,
            $eventName,
            $payload,
            $pushedOn
        );
        $this->publisher->publish(
            new AfterEventPersist(
                $id,
                $eventName,
                $payload,
                $pushedOn
            )
        );
    }

    /**
     * Dispatched before persist of the event in the DB.
     * Override to push more data using the $builder.
     * @see BeforeEventPersist if you need the information form outside the store.
     */
    protected function beforeEventPersist(
        string $id,
        string $eventName,
        Payload $payload,
        RowDatasetBuilder $builder,
        DateTimeInterface $pushedOn
    ): RowDatasetBuilder {
        return $builder;
    }

    /**
     * Dispatched after the event row is in the DB.
     * @see AfterEventPersist if you need the information form outside the store.
     */
    protected function afterEventPersist(
        string $id,
        string $eventName,
        Payload $payload,
        DateTimeInterface $pushedOn
    ): void {
    }
}
