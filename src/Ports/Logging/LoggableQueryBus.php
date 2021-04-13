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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QueryBus
     */
    private $bus;

    public function __construct(LoggerInterface $logger, QueryBus $bus)
    {
        $this->logger = $logger;
        $this->bus = $bus;
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
