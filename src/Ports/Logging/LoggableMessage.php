<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Logging;

use Psr\Log\LoggerInterface;

interface LoggableMessage
{
    public function logMessage(LoggerInterface $logger): void;
}
