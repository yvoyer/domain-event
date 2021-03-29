<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Logging;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Star\Component\DomainEvent\Messaging\Query;
use Star\Component\DomainEvent\Messaging\QueryBus;
use function sprintf;

final class LoggableQueryBusTest extends TestCase
{
    public function test_is_should_log_command_dispatch(): void
    {
        $query = $this->createMock(Query::class);
        $wrapped = $this->createMock(QueryBus::class);
        $logger = new TestLogger();
        $bus = new LoggableQueryBus($wrapped, $logger);
        $bus->dispatchQuery($query);

        $records = $logger->records;
        $this->assertCount(1, $records);
        $this->assertSame(
            sprintf('Dispatching the query "%s".', \get_class($query)), $records[0]['message']
        );
    }

    public function test_is_should_log_message_when_instance_of_loggable_message(): void
    {
        $query = new class() implements Query, LoggableMessage {
            public function logMessage(LoggerInterface $logger): void
            {
                $logger->debug('some query', ['id' => 3]);
            }

            public function __invoke($result): void
            {
                throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
            }

            public function getResult()
            {
                throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
            }
        };
        $wrapped = $this->createMock(QueryBus::class);
        $logger = new TestLogger();
        $bus = new LoggableQueryBus($wrapped, $logger);
        $bus->dispatchQuery($query);

        $records = $logger->records;
        $this->assertCount(1, $records);
        $this->assertSame(
            [
                'level' => 'debug',
                'message' => 'some query',
                'context' => [
                    'id' => 3,
                ],
            ],
            $records[0]
        );
    }
}
