<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Logging;

use Psr\Log\LoggerInterface;
use Star\Component\DomainEvent\Messaging\Query;
use Star\Component\DomainEvent\Messaging\QueryBus;
use function get_class;
use function sprintf;

final class LoggableQueryBus implements QueryBus
{
    /**
     * @var QueryBus
     */
    private $bus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(QueryBus $bus, LoggerInterface $logger)
    {
        $this->bus = $bus;
        $this->logger = $logger;
    }

    public function dispatchQuery(Query $query): void
    {
        if ($query instanceof LoggableMessage) {
            $query->logMessage($this->logger);
        } else {
            $this->logger->debug(sprintf('Dispatching the query "%s".', get_class($query)));
        }

        $this->bus->dispatchQuery($query);
    }
}
