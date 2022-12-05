<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use RuntimeException;
use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Star\Component\DomainEvent\Messaging\QueryBus;
use Star\Component\DomainEvent\Messaging\Results\CollectionQuery;
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
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Kernel;
use function array_map;
use function ob_get_clean;
use function ob_start;

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
                    'message' => 'Dispatching the command "Star\Component\DomainEvent\Ports\Stub\DoSomething".',
                    'context' => [],
                ],
            ],
            $logger->records
        );
    }

    public function test_it_should_use_loggable_query_bus(): void
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
        $builder->addCompilerPass(new QueryBusPass());
        $builder
            ->register(LoggerInterface::class, TestLogger::class)
            ->setPublic(true);
        $builder
            ->register(TestQueryController::class)
            ->addArgument(new Reference(QueryBus::class))
            ->setPublic(true);
        $builder
            ->register(DoNothingOnInvokeHandler::class)
            ->addTag('star.query_handler', ['message' => DoSomething::class]);

        $builder->compile();

        /**
         * @var TestQueryController $controller
         */
        $controller = $builder->get(TestQueryController::class);
        $controller->doQuery(new DoSomething('action'));

        /**
         * @var TestLogger $logger
         */
        $logger = $builder->get(LoggerInterface::class);
        self::assertSame(
            [
                [
                    'level' => 'debug',
                    'message' => 'Dispatching the query "Star\Component\DomainEvent\Ports\Stub\DoSomething".',
                    'context' => [],
                ],
            ],
            $logger->records
        );
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

    public function test_it_should_log_all_actions_with_all_passes_registered(): void {
        $kernel = new class('test', true) extends Kernel {
            protected function build(ContainerBuilder $container)
            {
                $container->register($serviceId = EventDispatcher::class, EventDispatcher::class);
                $container->addCompilerPass(new EventPublisherPass($serviceId));
                $container->addCompilerPass(new CommandBusPass());
                $container->addCompilerPass(new QueryBusPass());
                $container->loadFromExtension(
                    'domain_event',
                    [
                        'logging' => [
                            'logger_id' => LoggerInterface::class,
                        ],
                    ]
                );

                $container
                    ->register(CompleteStuffController::class)
                    ->setArguments(
                        [
                            new Reference(CommandBus::class),
                            new Reference(QueryBus::class),
                        ]
                    )
                    ->setPublic(true);
                $container
                    ->register(DoSomethingHandler::class)
                    ->addArgument(new Reference(EventStoreStub::class))
                    ->addTag('star.command_handler');
                $container
                    ->register(EventStoreStub::class)
                    ->addArgument(new Reference('star.event_publisher'));
                $container
                    ->register('processor.one', ProcessorStub::class)
                    ->setArguments([new Reference(CommandBus::class), 'one', 'two'])
                    ->addTag('star.event_listener');
                $container
                    ->register('processor.two', ProcessorStub::class)
                    ->setArguments([new Reference(CommandBus::class), 'one.two', 'three'])
                    ->addTag('star.event_listener');
                $container
                    ->register('processor.three', ProcessorStub::class)
                    ->setArguments([new Reference(CommandBus::class), 'one.two.three', 'final'])
                    ->addTag('star.event_listener');
                $container
                    ->register(SearchForLogsHandler::class)
                    ->setArguments(
                        [
                            new Reference(LoggerInterface::class),
                        ]
                    )
                    ->addTag('star.query_handler')
                ;
            }

            public function registerBundles(): array
            {
                return [new DomainEventBundle()];
            }

            public function registerContainerConfiguration(LoaderInterface $loader): ContainerBuilder
            {
                $builder = new ContainerBuilder();
                $builder->register('event_dispatcher', EventDispatcher::class);
                $builder->register(LoggerInterface::class, TestLogger::class);

                return $builder;
            }
        };
        $kernel->boot();
        $container = $kernel->getContainer();

        /**
         * @var CompleteStuffController $service
         */
        $service = $container->get(CompleteStuffController::class);
        ob_start();
        $result = $service->doStuff();
        ob_get_clean();

        self::assertSame(
            [
                'Listener "Star\Component\DomainEvent\Ports\Stub\ProcessorStub" was registered for subscribing to events.',
                'Listener "Star\Component\DomainEvent\Ports\Stub\ProcessorStub" was registered for subscribing to events.',
                'Listener "Star\Component\DomainEvent\Ports\Stub\ProcessorStub" was registered for subscribing to events.',
                'Dispatching the command "Star\Component\DomainEvent\Ports\Stub\DoSomething".',
                'Event "Star\Component\DomainEvent\Ports\Stub\ActionWasDone" was published.',
                'Dispatching the command "Star\Component\DomainEvent\Ports\Stub\DoSomething".',
                'Event "Star\Component\DomainEvent\Ports\Stub\ActionWasDone" was published.',
                'Dispatching the command "Star\Component\DomainEvent\Ports\Stub\DoSomething".',
                'Event "Star\Component\DomainEvent\Ports\Stub\ActionWasDone" was published.',
                'Dispatching the command "Star\Component\DomainEvent\Ports\Stub\DoSomething".',
                'Event "Star\Component\DomainEvent\Ports\Stub\ActionWasDone" was published.',
                'Dispatching the query "Star\Component\DomainEvent\Ports\Symfony\SearchForLogs".',
            ],
            $result
        );
    }
}

final class SearchForLogs extends CollectionQuery {
}

final class SearchForLogsHandler
{
    /**
     * @var TestLogger
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(SearchForLogs $query): void
    {
        $query(
            array_map(
                function (array $row): string {
                    return $row['message'];
                },
                $this->logger->recordsByLevel['debug']
            )
        );
    }
}

final class CompleteStuffController
{
    /**
     * @var CommandBus
     */
    private $commands;

    /**
     * @var QueryBus
     */
    private $queries;

    public function __construct(
        CommandBus $commands,
        QueryBus $queries
    ) {
        $this->commands = $commands;
        $this->queries = $queries;
    }

    public function doStuff(): array
    {
        $this->commands->dispatchCommand(new DoSomething('one'));
        $this->queries->dispatchQuery($query = new SearchForLogs());

        return $query->getResult();
    }
}
