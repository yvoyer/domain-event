--TEST--
Complete example of usage in a basic app.
--FILE--
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Star\Component\DomainEvent\Messaging\MessageMapBus;
use Star\Component\DomainEvent\Ports\Symfony\SymfonyPublisher;
use Star\Example\Blog\Application\Bridge\Event\UserWasRegistered;
use Star\Example\Blog\Application\Command as AppCommands;
use Star\Example\Blog\Application\Http\Controller\PostController;
use Star\Example\Blog\Application\Processor;
use Star\Example\Blog\Domain\Command\Blog as BlogCommands;
use Star\Example\Blog\Domain\Command\Post as PostCommands;
use Star\Example\Blog\Domain\Query\Post as PostQueries;
use Star\Example\Blog\Infrastructure\Persistence\InMemory\PostCollection;
use Symfony\Component\EventDispatcher\EventDispatcher;

// Setup application. Would normally be in a DI container, or in a bootstrap script
$messageBus = new MessageMapBus();
$publisher = new SymfonyPublisher(new EventDispatcher());
$posts = new PostCollection();

$messageBus->registerHandler(
    AppCommands\RegisterUser::class,
    new AppCommands\RegisterUserHandler($publisher)
);
$messageBus->registerHandler(
    BlogCommands\CreateBlog::class,
    new BlogCommands\CreateBlogHandler($publisher)
);
$messageBus->registerHandler(
    PostCommands\CreateNewPost::class,
    new PostCommands\CreateNewPostHandler($posts, $publisher)
);
$messageBus->registerHandler(
    PostCommands\PublishPost::class,
    new PostCommands\PublishPostHandler($posts, $publisher)
);
$messageBus->registerHandler(
    PostQueries\SearchForPost::class,
    $postIndex = new PostQueries\SearchForPostHandler()
);

$publisher->subscribe(new Processor\CreateBlogOnUserRegister($messageBus));
$publisher->subscribe($postIndex);

$controller = new PostController($messageBus, $messageBus);

// Requests made by a user to the server
echo $controller->search('cqrs') . "\n";

$publisher->publish(new UserWasRegistered($blogName = 'my-blog'));

echo $controller->createPost($blogName, ['data' => ['title' => 'DDD rocks !!!']]) . "\n"; // not published
echo $controller->createPost($blogName, ['data' => ['title' => 'Example for cqrs concepts.']]) . "\n"; // published
echo $controller->createPost($blogName, ['data' => ['title' => 'Another DDD post.']]) . "\n"; // published

$controller->publish(2);
$controller->publish(3);

echo $controller->search('cqrs') . "\n";
echo $controller->search('DDD') . "\n";

?>
--EXPECTF--
Searching: cqrs.
[]
{"id":"1"}
{"id":"2"}
{"id":"3"}
Searching: cqrs.
[{"id":"2","title":"Example for cqrs concepts.","blogName":"my-blog","published":true,"publishedAt":"2000-01-01","publishedBy":"username"}]
Searching: DDD.
[{"id":"3","title":"Another DDD post.","blogName":"my-blog","published":true,"publishedAt":"2000-01-01","publishedBy":"username"}]
