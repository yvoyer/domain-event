<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Stub;

use Star\Component\DomainEvent\Messaging\CommandBus;

final class TestCommandController
{
    /**
     * @var CommandBus
     */
    private $bus;

    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    public function doAction(string $action): void
    {
        $this->bus->dispatchCommand(new DoSomething($action));
    }
}
