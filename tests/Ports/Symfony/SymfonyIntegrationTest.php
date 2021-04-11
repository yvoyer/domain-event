<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use RuntimeException;
use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Star\Component\DomainEvent\Messaging\QueryBus;
use Star\Component\DomainEvent\Ports\Logging\LoggablePublisher;
use Star\Component\DomainEvent\Ports\Stub\ActionWasDone;
use Star\Component\DomainEvent\Ports\Stub\DoSomething;
use Star\Component\DomainEvent\Ports\Stub\DoNothingOnInvokeHandler;
use Star\Component\DomainEvent\Ports\Stub\TestCommandController;
use Star\Component\DomainEvent\Ports\Stub\DoSomethingHandler;
use Star\Component\DomainEvent\Ports\Stub\EventStoreStub;
use Star\Component\DomainEvent\Ports\Stub\ProcessorStub;
use Star\Component\DomainEvent\Ports\Stub\TestEventController;
use Star\Component\DomainEvent\Ports\Stub\TestQueryController;
use Star\Component\DomainEvent\Ports\Stub\ThrowExceptionOnInvokeHandler;
use Star\Component\DomainEvent\Ports\Symfony\DependencyInjection\CommandBusPass;
use Star\Component\DomainEvent\Ports\Symfony\DependencyInjection\DomainEventExtension;
use Star\Component\DomainEvent\Ports\Symfony\DependencyInjection\EventPublisherPass;
use Star\Component\DomainEvent\Ports\Symfony\DependencyInjection\QueryBusPass;
use Star\Component\DomainEvent\Ports\Symfony\DependencyInjection\SomethingWasDone;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class SymfonyIntegrationTest extends TestCase
{
    public function test_it_should_use_default_configuration_for_command_bus(): void
    {
        $extension = new DomainEventExtension();
        $configs = [];
        $extension->load($configs, $builder = new ContainerBuilder());
        $builder->addCompilerPass(new CommandBusPass());
        $builder
            ->register(TestCommandController::class)
            ->addArgument(new Reference(CommandBus::class))
            ->setPublic(true);
        $builder
            ->register(ThrowExceptionOnInvokeHandler::class)
            ->addTag('star.command_handler', ['message' => DoSomething::class]);

        $builder->compile();

        /**
         * @var TestCommandController $controller
         */
        $controller = $builder->get(TestCommandController::class);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected message: "' . DoSomething::class);
        $controller->doAction('action');
    }

    public function test_it_should_use_default_configuration_for_query_bus(): void
    {
        $extension = new DomainEventExtension();
        $configs = [];
        $extension->load($configs, $builder = new ContainerBuilder());
        $builder->addCompilerPass(new QueryBusPass());
        $builder
            ->register(TestQueryController::class)
            ->addArgument(new Reference(QueryBus::class))
            ->setPublic(true);
        $builder
            ->register(ThrowExceptionOnInvokeHandler::class)
            ->addTag('star.query_handler', ['message' => DoSomething::class]);

        $builder->compile();

        /**
         * @var TestQueryController $controller
         */
        $controller = $builder->get(TestQueryController::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected message: "' . DoSomething::class);
        $controller->doQuery(new DoSomething('action'));
    }

    public function test_it_should_use_default_configuration_for_publisher(): void
    {
        $extension = new DomainEventExtension();
        $configs = [];
        $extension->load($configs, $builder = new ContainerBuilder());
        $builder->register('event_dispatcher', EventDispatcher::class);
        $builder->addCompilerPass(new EventPublisherPass());
        $builder
            ->register(TestEventController::class)
            ->addArgument(new Reference(EventPublisher::class))
            ->setPublic(true);
        $builder
            ->register(ThrowExceptionOnInvokeHandler::class)
            ->addTag('star.event_listener');

        $builder->compile();

        /**
         * @var TestEventController $controller
         */
        $controller = $builder->get(TestEventController::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected message: "' . ActionWasDone::class);
        $controller->triggerEvent(new ActionWasDone('action'));
    }

    public function test_it_should_allow_to_use_loggable_command_bus(): void
    {
        $extension = new DomainEventExtension();
        $configs = [
            'domain-event' => [
                'logging' => [
                    'logger_id' => LoggerInterface::class,
                ],
            ],
        ];
        $extension->load($configs, $builder = new ContainerBuilder());
        $builder->addCompilerPass(new CommandBusPass());
        $builder
            ->register(LoggerInterface::class, TestLogger::class)
            ->setPublic(true);
        $builder
            ->register(TestCommandController::class)
            ->addArgument(new Reference(CommandBus::class))
            ->setPublic(true);
        $builder
            ->register(DoNothingOnInvokeHandler::class)
            ->addArgument(new Reference(LoggerInterface::class))
            ->addTag('star.command_handler', ['message' => DoSomething::class]);

        $builder->compile();

        /**
         * @var TestCommandController $controller
         */
        $controller = $builder->get(TestCommandController::class);
        $controller->doAction('action');

        /**
         * @var TestLogger $logger
         */
        $logger = $builder->get(LoggerInterface::class);
        self::assertSame(
            [
                [
                    'level' => 'debug',
                    'message' => '',
                    'context' => [],
                ],
            ],
            $logger->records
        );
    }

    public function test_it_should_use_loggable_query_bus(): void
    {
        $this->fail('todo');
    }

    public function test_it_should_use_loggable_publisher(): void
    {
        $extension = new DomainEventExtension();
        $configs = [
            'domain-event' => [
                'logging' => [
                    'logger_id' => LoggerInterface::class,
                ],
            ],
        ];
        $extension->load($configs, $builder = new ContainerBuilder());
        $builder->addCompilerPass(new EventPublisherPass());
        $builder
            ->register(LoggerInterface::class, TestLogger::class)
            ->setPublic(true);
        $builder
            ->register('event_dispatcher', EventDispatcher::class);
        $builder
            ->register(TestEventController::class)
            ->addArgument(new Reference(EventPublisher::class))
            ->setPublic(true);

        $builder->compile();

        /**
         * @var TestEventController $controller
         */
        $controller = $builder->get(TestEventController::class);
        $controller->triggerEvent(new ActionWasDone('action'));

        /**
         * @var TestLogger $logger
         */
        $logger = $builder->get(LoggerInterface::class);
        self::assertSame(
            [
                [
                    'level' => 'debug',
                    'message' => 'Event "Star\Component\DomainEvent\Ports\Stub\ActionWasDone" was published.',
                    'context' => [],
                ],
            ],
            $logger->records
        );
    }

    public function test_it_should_send_event_dispatched_inside_command_handler_to_other_listeners(): void {
        $builder = new ContainerBuilder();
        $builder->register('other_dispatcher', EventDispatcher::class);
        $builder->addCompilerPass(
            new EventPublisherPass(
                'other_dispatcher',
                new Definition(LoggablePublisher::class, [new Reference('logger'), new Reference(Sy)])
            )
        );
        $builder->addCompilerPass(new CommandBusPass());
        $builder->addCompilerPass(new QueryBusPass());

        $builder
            ->register(TestCommandController::class)
            ->addArgument(new Reference(CommandBus::class))
            ->setPublic(true);
        $builder
            ->register(DoSomethingHandler::class)
            ->addArgument(new Reference(EventStoreStub::class))
            ->addTag('star.command_handler');
        $builder
            ->register(EventStoreStub::class)
            ->addArgument(new Reference('star.event_publisher'));
        $builder
            ->register('processor.one', ProcessorStub::class)
            ->setArguments([new Reference(CommandBus::class), 'one', 'two'])
            ->addTag('star.event_listener');
        $builder
            ->register('processor.two', ProcessorStub::class)
            ->setArguments([new Reference(CommandBus::class), 'one.two', 'three'])
            ->addTag('star.event_listener');
        $builder
            ->register('processor.three', ProcessorStub::class)
            ->setArguments([new Reference(CommandBus::class), 'one.two.three', 'final'])
            ->addTag('star.event_listener');
        $builder->compile();

        /**
         * @var TestCommandController $service
         */
        $service = $builder->get(TestCommandController::class);
        \ob_start();
        $service->doAction('one');
        $result = \ob_get_clean();
        $this->assertSame('one=>two;one.two=>three;one.two.three=>final;', $result);
    }
}
