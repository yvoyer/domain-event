<?php declare(strict_types=1);

namespace Star\Example\Blog\Application\Http\Controller;

use DateTime;
use Star\Example\Blog\Domain\Command\Post\CreateNewPost;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Star\Component\DomainEvent\Messaging\QueryBus;
use Star\Example\Blog\Domain\Command\Post\PublishPost;
use Star\Example\Blog\Domain\Model\BlogId;
use Star\Example\Blog\Domain\Model\Post\PostId;
use Star\Example\Blog\Domain\Model\Post\PostTitle;
use Star\Example\Blog\Domain\Query\Post\SearchForPost;
use function implode;
use function json_encode;
use function sprintf;

final class PostController
{
    private int $i = 0;

    public function __construct(
        private CommandBus $commandBus,
        private QueryBus $queryBus,
    ) {
    }

    public function publish(int $postId): string
    {
        $this->commandBus->dispatchCommand(
            new PublishPost(PostId::fromInt($postId), new DateTime('2000-01-01'), 'username')
        );

        return (string) json_encode(['success' => true]);
    }

    public function search(string ...$patterns): string
    {
        echo sprintf('Searching: %s.', implode(', ', $patterns)) . "\n";
        $query = new SearchForPost(...$patterns);

        $this->queryBus->dispatchQuery($query);

        return (string) json_encode($query->getResult());
    }

    /**
     * @param string $blogId
     * @param array{
     *     data: array{
     *         title: string,
     *     },
     * } $request
     * @return string
     */
    public function createPost(
        string $blogId,
        array $request,
    ): string {
        $this->i ++;
        $this->commandBus->dispatchCommand(
            new CreateNewPost(
                $id = PostId::fromString((string) $this->i),
                new PostTitle($request['data']['title']),
                new BlogId($blogId)
            )
        );

        return (string) json_encode(
            [
                'id' => $id->toString(),
            ]
        );
    }
}
