<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Star\Component\DomainEvent\Messaging\Command;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CommandBusPassTest extends TestCase
{
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

    public function test_it_should_throw_exception_when_handler_is_missing_handler_suffix(): void
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The handler "' . MalformedThatThrowsException::class . '" must have a "Handler" suffix.'
        );

        $builder->compile();
    }

    public function test_it_should_throw_exception_when_message_is_not_a_valid_class(): void
    {
        $builder = new ContainerBuilder();
        $builder->register(CommandController::class, CommandController::class)
            ->addArgument(new Reference('star.command_bus'))
            ->setPublic(true);
        $builder
            ->register('my_handler', 'stdClassHandler')
            ->addTag('star.command_handler', ['message' => 'bad-command'])
        ;
        $builder->addCompilerPass(new CommandBusPass());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The command "bad-command" must be a class implementing interface "' . Command::class
        );
        $builder->compile();
    }

    public function test_it_should_allow_to_define_custom_path(): void
    {
        $command = new class implements Command {};
        $builder = new ContainerBuilder();
        $builder->register(CommandController::class, CommandController::class)
            ->addArgument(new Reference('star.command_bus'))
            ->setPublic(true);
        $builder
            ->register('my_handler', DoStuffHandler::class)
            ->addTag('star.command_handler', ['message' => \get_class($command)])
        ;
        $builder->addCompilerPass(new CommandBusPass());
        $builder->compile();

        /**
         * @var CommandController $controller
         */
        $controller = $builder->get(CommandController::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Command: ' . \get_class($command));
        $controller->dispatch($command);
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

    public function dispatch(Command $command): void
    {
        $this->bus->dispatchCommand($command);
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
    public function __invoke($command): void
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
