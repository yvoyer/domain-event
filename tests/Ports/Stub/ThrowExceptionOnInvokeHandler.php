<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Stub;

use RuntimeException;
use Star\Component\DomainEvent\EventListener;

final class ThrowExceptionOnInvokeHandler implements EventListener
{
    public function __invoke($message): void
    {
        throw new RuntimeException(\sprintf('Expected message: "%s"', \get_class($message)));
    }

    public function onSomethingDone(ActionWasDone $event): void
    {
        $this->__invoke($event);
    }

    public function listensTo(): array
    {
        return [
            ActionWasDone::class => 'onSomethingDone',
        ];
    }
}
