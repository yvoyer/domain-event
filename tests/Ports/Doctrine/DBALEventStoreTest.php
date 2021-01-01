<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Doctrine;

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
