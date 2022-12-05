<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Stub;

use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\Messaging\CommandBus;

final class ProcessorStub implements EventListener
{
    /**
     * @var CommandBus
     */
    private $bus;

    /**
     * @var string
     */
    private $onAction;

    /**
     * @var string
     */
    private $dispatchAction;

    public function __construct(
        CommandBus $bus,
        string $onAction,
        string $dispatchAction
    ) {
        $this->bus = $bus;
        $this->onAction = $onAction;
        $this->dispatchAction = $dispatchAction;
    }

    public function onDoSomething(ActionWasDone $event): void
    {
        if ($event->action() === $this->onAction) {
            echo \sprintf('%s=>%s;', $this->onAction, $this->dispatchAction);
            $this->bus->dispatchCommand(
                new DoSomething(\sprintf('%s.%s', $this->onAction, $this->dispatchAction))
            );
        }
    }

    public function listensTo(): array
    {
        return [
            ActionWasDone::class => 'onDoSomething',
        ];
    }
}
