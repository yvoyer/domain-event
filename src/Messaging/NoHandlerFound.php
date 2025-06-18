<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Messaging;

use InvalidArgumentException;
use function sprintf;

final class NoHandlerFound extends InvalidArgumentException
{
    public function __construct(string $message)
    {
        parent::__construct(
            sprintf('No handler could be found for message "%s".', $message)
        );
    }
}
