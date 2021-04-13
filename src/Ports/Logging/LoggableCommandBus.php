<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Logging;

use Psr\Log\LoggerInterface;
use Star\Component\DomainEvent\Messaging\Command;
use Star\Component\DomainEvent\Messaging\CommandBus;
use function get_class;
use function sprintf;

final class LoggableCommandBus implements CommandBus
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CommandBus
     */
    private $bus;

    public function __construct(LoggerInterface $logger, CommandBus $bus)
    {
        $this->logger = $logger;
        $this->bus = $bus;
    }

    public function dispatchCommand(Command $command): void
    {
        if ($command instanceof LoggableMessage) {
            $command->logMessage($this->logger);
        } else {
            $this->logger->debug(sprintf('Dispatching the command "%s".', get_class($command)));
        }

        $this->bus->dispatchCommand($command);
    }
}
