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
     * @var CommandBus
     */
    private $bus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(CommandBus $bus, LoggerInterface $logger)
    {
        $this->bus = $bus;
        $this->logger = $logger;
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
