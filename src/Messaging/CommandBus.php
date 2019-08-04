<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Messaging;

interface CommandBus
{
    public function dispatchCommand(Command $command): void;
}
