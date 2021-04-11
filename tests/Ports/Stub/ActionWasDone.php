<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Stub;

use Star\Component\DomainEvent\DomainEvent;

final class ActionWasDone implements DomainEvent
{
    /**
     * @var string
     */
    private $action;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    public function action(): string
    {
        return $this->action;
    }
}
