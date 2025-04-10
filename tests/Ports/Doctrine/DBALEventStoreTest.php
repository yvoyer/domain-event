<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Star\Component\DomainEvent\AggregateRoot;
use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Serialization\Payload;
use Star\Component\DomainEvent\Serialization\PayloadFromReflection;
use Star\Example\Blog\Domain\Event\Post\PostTitleWasChanged;
use Star\Example\Blog\Domain\Event\Post\PostWasDrafted;
use Star\Example\Blog\Domain\Event\Post\PostWasPublished;
use Star\Example\Blog\Domain\Model\BlogId;
use Star\Example\Blog\Domain\Model\Post\PostAggregate;
use Star\Example\Blog\Domain\Model\Post\PostId;
use Star\Example\Blog\Domain\Model\Post\PostTitle;
use function extension_loaded;
use function key_exists;
use function sprintf;
use function var_dump;

final class DBALEventStoreTest extends TestCase
{
    const TABLE_NAME = 'post_events';

    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('Extension sqlite3 must be enabled.');
        }

        $this->connection = DriverManager::getConnection(
            [
                'url' => 'sqlite:///:memory:',
            ]
        );
    }

    public function test_it_should_create_table_when_it_do_not_exists(): void
    {
        $store = new PostEventStore(
            $this->connection,
            $this->createMock(EventPublisher::class),
            new PayloadFromReflection()
        );

        self::assertFalse($this->connection->getSchemaManager()->tablesExist(self::TABLE_NAME));

        $store->saveAggregate(
            PostAggregate::draftPost(
                $id = PostId::asUUID(),
                new PostTitle('Title'),
                new BlogId('blog')
            )
        );

        self::assertTrue($this->connection->getSchemaManager()->tablesExist(self::TABLE_NAME));
        $persisted = $store->loadAggregate($id->toString());

        self::assertInstanceOf(PostAggregate::class, $persisted);
        self::assertCount(0, $persisted->uncommitedEvents());
        self::assertSame($id->toString(), $persisted->getId()->toString());
    }

    public function test_it_should_throw_exception_when_no_aggregate_found(): void
    {
        $store = new PostEventStore(
            $this->connection,
            $this->createMock(EventPublisher::class),
            new PayloadFromReflection()
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Aggregate "not-found" not found.');
        $store->loadAggregate('not-found');
    }

    public function test_it_should_allow_to_order_events_by_custom_field(): void
    {
        $post = PostAggregate::draftPost(
            $postId = PostId::asUUID(),
            new PostTitle('Old'),
            BlogId::asUuid()
        );
        self::assertSame('Old', $post->getTitle()->toString());

        $post->changeTitle('Title in 2000', new DateTimeImmutable('2000-01-01'));
        $post->changeTitle('Title in 2001', new DateTimeImmutable('2001-01-01'));
        $post->changeTitle('Title in 2002', new DateTimeImmutable('2002-01-01'));

        self::assertSame('Title in 2002', $post->getTitle()->toString());

        $store = new PostEventStore(
            $this->connection,
            $this->createMock(EventPublisher::class),
            new PayloadFromReflection()
        );
        $store->saveAggregate($post);

        $qb = $this->connection->createQueryBuilder();
        $expr = $qb->expr();
        $qb
            ->update(DBALEventStoreTest::TABLE_NAME)
            ->set('pushed_on', ':new_date')
            ->where($expr->like('payload', ':pattern'))
            ->setParameter('new_date', '2000-01-01 00:00:00')
            ->setParameter('pattern', '%Title in 2002%')
            ->execute();

        $storeByVersion = new PostEventStore(
            $this->connection,
            $this->createMock(EventPublisher::class),
            new PayloadFromReflection()
        );
        $after = $storeByVersion->loadAggregate($postId->toString());
        self::assertSame(
            'Title in 2002',
            $after->getTitle()->toString(),
            'Should order by the version by default'
        );

        $storeByDate = new PostEventStore(
            $this->connection,
            $this->createMock(EventPublisher::class),
            new PayloadFromReflection(),
            [
                'pushed_on',
            ]
        );
        $after = $storeByDate->loadAggregate($postId->toString());
        self::assertSame(
            'Title in 2001',
            $after->getTitle()->toString(),
            'Should order by the date instead of version'
        );
    }

    public function test_it_should_allow_to_use_custom_date_when_persisting_event(): void
    {
        $store = new class(
            $this->connection,
            $this->createMock(EventPublisher::class),
            new PayloadFromReflection()
        ) extends DBALEventStore {
            public function saveEvent(PostAggregate $aggregate): void
            {
                $this->persistAggregate($aggregate->getId()->toString(), $aggregate);
            }

            protected function createPushedOnDateFromPayload(array $payload): DateTimeImmutable
            {
                if (key_exists('changedAt', $payload)) {
                    return new DateTimeImmutable($payload['changedAt']);
                }

                return parent::createPushedOnDateFromPayload($payload);
            }

            protected function tableName(): string
            {
                return DBALEventStoreTest::TABLE_NAME;
            }

            protected function createAggregateFromStream(array $events): AggregateRoot
            {
                throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
            }

            protected function handleNoEventFound(string $id): void
            {
                throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
            }
        };

        $post = PostAggregate::draftPost(
            PostId::asUUID(),
            PostTitle::randomTitle(),
            BlogId::asUuid()
        );
        $store->saveEvent($post);

        $post->changeTitle(
            'New title',
            new DateTimeImmutable('2000-01-01')
        );

        $store->saveEvent($post);

        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME)
            ->execute()
            ->fetchAll();
        self::assertCount(2, $result);
        self::assertSame(PostWasDrafted::class, $result[0]['event_name']);
        self::assertSame(PostTitleWasChanged::class, $result[1]['event_name']);
        self::assertSame('2000-01-01 00:00:00', $result[1]['pushed_on']);
    }

    public function test_it_should_use_dynamic_version(): void
    {
        $store = new PostEventStore(
            $this->connection,
            $this->createMock(EventPublisher::class),
            new PayloadFromReflection()
        );
        $post = PostAggregate::draftPostFixture();
        $post->changeTitle('Change 1', new DateTimeImmutable());
        $post->publish(new DateTimeImmutable(), 'Joe');
        $post->changeTitle('Change 2', new DateTimeImmutable());

        $store->saveAggregate($post);

        /**
         * @var array<int, array{ event_name: string, version: int }> $result
         */
        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME)
            ->execute()
            ->fetchAllAssociative();

        self::assertSame(PostWasDrafted::class, $result[0]['event_name']);
        self::assertSame(1, (int) $result[0]['version']);
        self::assertSame(PostTitleWasChanged::class, $result[1]['event_name']);
        self::assertSame(2, (int) $result[1]['version']);
        self::assertSame(PostWasPublished::class, $result[2]['event_name']);
        self::assertSame(3, (int) $result[2]['version']);
        self::assertSame(PostTitleWasChanged::class, $result[3]['event_name']);
        self::assertSame(4, (int) $result[3]['version']);
    }

    public function test_it_should_allow_to_add_more_data_to_event_table(): void
    {
        $store = new class(
            $this->connection,
            $this->createMock(EventPublisher::class),
            new PayloadFromReflection()
        ) extends DBALEventStore {
            protected function tableName(): string
            {
                return DBALEventStoreTest::TABLE_NAME;
            }

            protected function createAggregateFromStream(array $events): AggregateRoot
            {
                throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
            }

            protected function handleNoEventFound(string $id): void
            {
                throw new RuntimeException(\sprintf('Aggregate "%s" not found.', $id));
            }

            public function saveAggregate(PostAggregate $post): void
            {
                $this->persistAggregate($post->getId()->toString(), $post);
            }

            protected function buildDatasetRow(
                Payload $payload,
                RowDatasetBuilder $builder
            ): RowDatasetBuilder {
                if ($payload->keyExists('changedAt')) {
                    $builder->setStringColumn(
                        'same_as_name',
                        $payload->getString('changedAt')
                    );
                }

                return parent::buildDatasetRow($payload, $builder);
            }
        };
        $post = PostAggregate::draftPostFixture();
        $store->saveAggregate($post);

        $this->connection
            ->executeQuery(
                sprintf(
                    'ALTER TABLE %s ADD COLUMN same_as_name DEFAULT NULL',
                    self::TABLE_NAME
                )
            );

        $post->changeTitle('New title', new DateTimeImmutable('2000-01-01'));
        $store->saveAggregate($post);

        /**
         * @var array<int, array{ new_column: null|string }> $result
         */
        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME)
            ->execute()
            ->fetchAllAssociative();

        self::assertCount(2, $result);
        self::assertNull($result[0]['same_as_name']);
        self::assertSame('2000-01-01 00:00:00.000000', $result[1]['same_as_name']);
    }

    public function test_it_should_allow_to_add_more_data_based_on_pattern_in_payload(): void
    {
        $store = new class(
            $this->connection,
            $this->createMock(EventPublisher::class),
            new PayloadFromReflection()
        ) extends DBALEventStore {
            protected function tableName(): string
            {
                return DBALEventStoreTest::TABLE_NAME;
            }

            protected function createAggregateFromStream(array $events): AggregateRoot
            {
                throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
            }

            protected function handleNoEventFound(string $id): void
            {
                throw new RuntimeException(\sprintf('Aggregate "%s" not found.', $id));
            }

            public function saveAggregate(PostAggregate $post): void
            {
                $this->persistAggregate($post->getId()->toString(), $post);
            }

            protected function buildDatasetRow(
                Payload $payload,
                RowDatasetBuilder $builder
            ): RowDatasetBuilder {
                if ($payload->keyContainsString('At')) {
                    $builder->setBooleanColumn(
                        'matching_pattern',
                        true
                    );
                }

                return parent::buildDatasetRow($payload, $builder);
            }
        };
        $post = PostAggregate::draftPostFixture();
        $store->saveAggregate($post);

        $this->connection
            ->executeQuery(
                sprintf(
                    'ALTER TABLE %s ADD COLUMN matching_pattern DEFAULT NULL',
                    self::TABLE_NAME
                )
            );

        $post->changeTitle('New title', new DateTimeImmutable('2000-01-01'));
        $store->saveAggregate($post);

        /**
         * @var array<int, array{ new_column: null|string }> $result
         */
        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME)
            ->execute()
            ->fetchAllAssociative();

        self::assertCount(2, $result);
        self::assertNull($result[0]['matching_pattern']);
        self::assertSame(1, $result[1]['matching_pattern']);
    }
}

final class PostEventStore extends DBALEventStore
{
    protected function tableName(): string
    {
        return DBALEventStoreTest::TABLE_NAME;
    }

    protected function createAggregateFromStream(array $events): AggregateRoot
    {
        return PostAggregate::fromStream($events);
    }

    protected function handleNoEventFound(string $id): void
    {
        throw new RuntimeException(\sprintf('Aggregate "%s" not found.', $id));
    }

    public function loadAggregate(string $id): PostAggregate
    {
        return $this->getAggregateWithId($id);
    }

    public function saveAggregate(PostAggregate $post): void
    {
        $this->persistAggregate($post->getId()->toString(), $post);
    }
}
