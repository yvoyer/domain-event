<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Messaging;

use PHPUnit\Framework\TestCase;

final class MessageMapBusTest extends TestCase
{
    private MessageMapBus $bus;

    public function setUp(): void
    {
        $this->bus = new MessageMapBus();
    }

    public function test_it_should_dispatch_command(): void
    {
        $message = $this->createMock(Command::class);
        $triggered = false;
        $this->bus->registerHandler(\get_class($message), function () use (&$triggered) { $triggered = true; });

        $this->assertFalse($triggered);

        $this->bus->dispatchCommand($message);

        $this->assertTrue($triggered);
    }

    public function test_it_should_throw_exception_when_command_not_supported(): void
    {
        $this->expectException(NoHandlerFound::class);
        $this->expectExceptionMessage('No handler could be found for message "Mock_Command_');

        $this->bus->dispatchCommand($this->createMock(Command::class));
    }

    public function test_it_should_dispatch_query(): void
    {
        $message = $this->createMock(Query::class);
        $triggered = false;
        $this->bus->registerHandler(\get_class($message), function () use (&$triggered) { $triggered = true; });

        $this->assertFalse($triggered);

        $this->bus->dispatchQuery($message);

        $this->assertTrue($triggered);
    }

    public function test_it_should_throw_exception_when_query_not_supported(): void
    {
        $this->expectException(NoHandlerFound::class);
        $this->expectExceptionMessage('No handler could be found for message "Mock_Query_');

        $this->bus->dispatchQuery($this->createMock(Query::class));
    }
}
