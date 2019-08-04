<?php declare(strict_types=1);

namespace Star\Example\Blog\Application\Http\Controller;

use Star\Example\Blog\Domain\Command\Post\CreateNewPost;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Star\Component\DomainEvent\Messaging\QueryBus;
use Star\Example\Blog\Domain\Command\Post\PublishPost;
use Star\Example\Blog\Domain\Model\BlogId;
use Star\Example\Blog\Domain\Model\Post\PostId;
use Star\Example\Blog\Domain\Model\Post\PostTitle;
use Star\Example\Blog\Domain\Query\Post\SearchForPost;

final class PostController
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var QueryBus
     */
    private $queryBus;

    /**
     * @var int
     */
    private $i = 0;

    public function __construct(CommandBus $commandBus, QueryBus $queryBus)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    public function publish(int $postId): string
    {
        $this->commandBus->dispatchCommand(
            new PublishPost(PostId::fromInt($postId), new \DateTime('2000-01-01'), 'username')
        );

        return \json_encode(['success' => true]);
    }

    public function search(string ...$patterns): string
    {
        echo \sprintf('Searching: %s.', \implode(', ', $patterns)) . "\n";
        $query = new SearchForPost(...$patterns);

        $this->queryBus->dispatchQuery($query);

        return \json_encode($query->getResult());
    }

    public function createPost(string $blogId, array $request): string
    {
        $this->i ++;
        $this->commandBus->dispatchCommand(
            new CreateNewPost(
                $id = PostId::fromString((string) $this->i),
                new PostTitle($request['data']['title']),
                new BlogId($blogId)
            )
        );

        return \json_encode(
            [
                'id' => $id->toString(),
            ]
        );
    }
}
