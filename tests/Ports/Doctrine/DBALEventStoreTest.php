<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Star\Component\DomainEvent\AggregateRoot;
use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Serialization\PayloadFromReflection;
use Star\Example\Blog\Domain\Model\BlogId;
use Star\Example\Blog\Domain\Model\Post\PostAggregate;
use Star\Example\Blog\Domain\Model\Post\PostId;
use Star\Example\Blog\Domain\Model\Post\PostTitle;
use function extension_loaded;

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
