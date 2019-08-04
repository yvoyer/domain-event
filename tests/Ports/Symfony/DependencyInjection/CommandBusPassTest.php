<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Star\Component\DomainEvent\Messaging\Command;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CommandBusPassTest extends TestCase
{
    /**
     * @var CommandBusPass
     */
    private $_commandBusPass;

    public function setUp(): void
    {
        $this->_commandBusPass = new CommandBusPass();
    }

    public function test_it_should_dispatch_command(): void
    {
        $builder = new ContainerBuilder();
        $builder->register(CommandController::class, CommandController::class)
            ->addArgument(new Reference('star.command_bus'))
            ->setPublic(true);
        $builder
            ->register('my_handler', DoStuffHandler::class)
            ->addTag('star.command_handler')
        ;
        $builder->addCompilerPass(new CommandBusPass());
        $builder->compile();

        /**
         * @var CommandController $controller
         */
        $controller = $builder->get(CommandController::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Command: ' . DoStuff::class);

        $controller->doStuff();
    }

    public function test_it_should_throw_exception_when_handler_is_not_correct_format(): void
    {
        $builder = new ContainerBuilder();
        $builder->register(CommandController::class, CommandController::class)
            ->addArgument(new Reference('star.command_bus'))
            ->setPublic(true);
        $builder
            ->register('my_handler', MalformedThatThrowsException::class)
            ->addTag('star.command_handler')
        ;
        $builder->addCompilerPass(new CommandBusPass());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'The command handler "' . MalformedThatThrowsException::class
            . '" must have a "Handler" suffix and a command matching the handler name without the suffix.'
        );

        $builder->compile();
    }
}

final class CommandController
{
    /**
     * @var CommandBus
     */
    private $bus;

    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    public function doStuff(): void
    {
        $this->bus->dispatchCommand(new DoStuff());
    }

    public function invokeMalformedHandler(): void
    {
        $this->bus->dispatchCommand(new MalformedThatThrowsException());
    }
}

final class DoStuff implements Command
{}
final class DoStuffHandler
{
    public function __invoke(DoStuff $command): void
    {
        throw new \RuntimeException('Command: ' . \get_class($command));
    }
}
final class MalformedThatThrowsException implements Command
{
    public function __invoke(MalformedThatThrowsException $command): void
    {
    }
}
