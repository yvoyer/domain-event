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
     * @param EventListener $listener
     *
     * @return BadMethodCallException
     */
    public static function methodNotDefinedOnListener(string $method, EventListener $listener): self
    {
        $listenerClass = \get_class($listener);

        return new self("The method '{$method}' do not exists on listener '{$listenerClass}'.");
    }
}
