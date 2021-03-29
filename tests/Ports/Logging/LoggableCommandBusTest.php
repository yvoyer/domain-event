<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Logging;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Star\Component\DomainEvent\Messaging\Command;
use Star\Component\DomainEvent\Messaging\CommandBus;
use function sprintf;

final class LoggableCommandBusTest extends TestCase
{
    public function test_is_should_log_command_dispatch(): void
    {
        $command = $this->createMock(Command::class);
        $wrapped = $this->createMock(CommandBus::class);
        $logger = new TestLogger();
        $bus = new LoggableCommandBus($wrapped, $logger);
        $bus->dispatchCommand($command);

        $records = $logger->records;
        $this->assertCount(1, $records);
        $this->assertSame(
            sprintf('Dispatching the command "%s".', \get_class($command)), $records[0]['message']
        );
    }

    public function test_is_should_log_message_when_instance_of_loggable_message(): void
    {
        $command = new class() implements Command, LoggableMessage {
            public function logMessage(LoggerInterface $logger): void
            {
                $logger->debug('some message', ['id' => 3]);
            }
        };
        $wrapped = $this->createMock(CommandBus::class);
        $logger = new TestLogger();

        $bus = new LoggableCommandBus($wrapped, $logger);
        $bus->dispatchCommand($command);

        $records = $logger->records;
        $this->assertCount(1, $records);
        $this->assertSame(
            [
                'level' => 'debug',
                'message' => 'some message',
                'context' => [
                    'id' => 3,
                ],
            ],
            $records[0]
        );
    }
}
