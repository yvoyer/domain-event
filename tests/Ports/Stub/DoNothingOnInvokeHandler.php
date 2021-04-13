<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Stub;

final class DoNothingOnInvokeHandler
{
    public function __invoke(): void
    {
        // do nothing
    }
}
