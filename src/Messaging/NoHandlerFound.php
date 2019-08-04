<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Messaging;

final class NoHandlerFound extends \InvalidArgumentException
{
    public function __construct(string $message)
    {
        parent::__construct(
            \sprintf('No handler could be found for message "%s".', $message)
        );
    }
}
