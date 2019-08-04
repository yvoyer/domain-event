<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Messaging;

interface Query extends Message
{
    public function __invoke($result): void;

    /**
     * @return mixed
     */
    public function getResult();
}
