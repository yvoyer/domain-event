<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Messaging;

interface QueryBus
{
    public function dispatchQuery(Query $query): void;
}
