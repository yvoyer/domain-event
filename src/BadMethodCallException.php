<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent;

use InvalidArgumentException;

final class BadMethodCallException extends InvalidArgumentException
{
    public static function methodNotDefinedOnListener(
        string $method,
        string $listener,
    ): self {
        return new self("The method '{$method}' do not exists on listener '{$listener}'.");
    }
}
