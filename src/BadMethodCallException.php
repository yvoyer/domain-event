<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent;

final class BadMethodCallException extends \InvalidArgumentException
{
    /**
     * @param string $method
     * @param class-string<EventListener> $listener
     *
     * @return BadMethodCallException
     */
    public static function methodNotDefinedOnListener(
        string $method,
        string $listener
    ): self {
        return new self("The method '{$method}' do not exists on listener '{$listener}'.");
    }
}
