<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Fixtures\Blog\Event\BlogWasCreated;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class EventPublisherPassTest extends TestCase
{
    public function test_it_should_pass_domain_event_to_listener_using_publisher_fqcn(): void
    {
        $builder = new ContainerBuilder();
        $builder->register('event_dispatcher', EventDispatcher::class);
        $builder->addCompilerPass(new EventPublisherPass());
        $builder->register(SomeListener::class, SomeListener::class)->addTag('star.event_listener');
        $builder->register(MyController::class, MyController::class)
            ->addArgument(new Reference(EventPublisher::class))
            ->setPublic(true);
        $builder->compile();

        /**
         * @var MyController $service
         */
        $service = $builder->get(MyController::class);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('My blog works!!');
        $service->doAction('My blog');
    }

    public function test_it_should_pass_domain_event_to_listener_using_short_alias(): void
    {
        $builder = new ContainerBuilder();
        $builder->register('event_dispatcher', EventDispatcher::class);
        $builder->addCompilerPass(new EventPublisherPass());
        $builder->register(SomeListener::class, SomeListener::class)->addTag('star.event_listener');
        $builder->register(MyController::class, MyController::class)
            ->addArgument(new Reference('star.event_publisher'))
            ->setPublic(true);
        $builder->compile();

        /**
         * @var MyController $service
         */
        $service = $builder->get(MyController::class);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('My blog works!!');
        $service->doAction('My blog');
    }
}

final class MyController
{
    /**
     * @var EventPublisher
     */
    private $publisher;

    public function __construct(EventPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function doAction(string $name): void
    {
        $this->publisher->publish(new BlogWasCreated($name));
    }
}

final class SomeListener implements EventListener
{
    public function onBlogCreate(BlogWasCreated $event): void
    {
        throw new \RuntimeException($event->blogName() . ' works!!');
    }

    public function listensTo(): array
    {
        return [
            BlogWasCreated::class => 'onBlogCreate',
        ];
    }
}
