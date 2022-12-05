<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Stub;

use Star\Component\DomainEvent\Messaging\Command;
use Star\Component\DomainEvent\Messaging\Query;

final class DoSomething implements Command, Query
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

    public function __invoke($result): void
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function getResult()
    {
        return $this->action;
    }
}
