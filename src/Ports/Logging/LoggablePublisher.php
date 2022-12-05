<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Logging;

use Psr\Log\LoggerInterface;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\EventPublisher;
use function sprintf;

final class LoggablePublisher implements EventPublisher
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventPublisher
     */
    private $publisher;

    public function __construct(LoggerInterface $logger, EventPublisher $publisher)
    {
        $this->logger = $logger;
        $this->publisher = $publisher;
    }

    public function subscribe(EventListener $listener): void
    {
        $this->logger->debug(
            sprintf(
                'Listener "%s" was registered for subscribing to events.',
                \get_class($listener)
            )
        );
        $this->publisher->subscribe($listener);
    }

    public function publish(DomainEvent $event): void
    {
        $this->logger->debug(sprintf('Event "%s" was published.', \get_class($event)));
        $this->publisher->publish($event);
    }

    public function publishChanges(array $events): void
    {
        $this->logger->debug(sprintf('Publishing changes of "%s" events.', \count($events)));

        foreach ($events as $event) {
            $this->publish($event);
        }
    }
}
