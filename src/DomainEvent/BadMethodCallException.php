<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
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
    public static function methodNotDefinedOnListener($method, EventListener $listener)
    {
        $listenerClass = get_class($listener);

        return new self("The method '{$method}' do not exists on listener '{$listenerClass}'.");
    }
}
