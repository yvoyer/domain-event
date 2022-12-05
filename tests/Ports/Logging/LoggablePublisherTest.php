<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Logging;

use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Ports\Stub\ActionWasDone;
use Star\Component\DomainEvent\Ports\Stub\ThrowExceptionOnInvokeHandler;

final class LoggablePublisherTest extends TestCase
{
    public function test_it_should_log_publish_of_changes(): void
    {
        $logger = new TestLogger();
        $wrapped = $this->createMock(EventPublisher::class);
        $publisher = new LoggablePublisher($logger, $wrapped);
        $publisher->publishChanges([new ActionWasDone('action')]);

        self::assertSame(
            [
                [
                    'level' => 'debug',
                    'message' => 'Publishing changes of "1" events.',
                    'context' => [],
                ],
                [
                    'level' => 'debug',
                    'message' => 'Event "Star\Component\DomainEvent\Ports\Stub\ActionWasDone" was published.',
                    'context' => [],
                ],
            ],
            $logger->records
        );
    }

    public function test_it_should_log_publishing_of_event(): void
    {
        $logger = new TestLogger();
        $wrapped = $this->createMock(EventPublisher::class);
        $wrapped
            ->expects(self::once())
            ->method('publish');
        $publisher = new LoggablePublisher($logger, $wrapped);
        $publisher->publish(new ActionWasDone('action'));

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

    public function test_it_should_log_subscribing(): void
    {
        $logger = new TestLogger();
        $wrapped = $this->createMock(EventPublisher::class);
        $wrapped
            ->expects(self::once())
            ->method('subscribe');
        $publisher = new LoggablePublisher($logger, $wrapped);
        $publisher->subscribe(new ThrowExceptionOnInvokeHandler());

        self::assertSame(
            [
                [
                    'level' => 'debug',
                    'message' => 'Listener "Star\Component\DomainEvent\Ports\Stub\ThrowExceptionOnInvokeHandler" was registered for subscribing to events.',
                    'context' => [],
                ],
            ],
            $logger->records
        );
    }
}
