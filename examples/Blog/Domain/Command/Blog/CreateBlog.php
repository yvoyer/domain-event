<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Command\Blog;

use Star\Component\DomainEvent\Messaging\Command;
use Star\Example\Blog\Domain\Model\BlogId;

final class CreateBlog implements Command
{
    /**
     * @var BlogId
     */
    private $blogId;

    public function __construct(BlogId $blogId)
    {
        $this->blogId = $blogId;
    }

    public function blogId(): BlogId
    {
        return $this->blogId;
    }
}
