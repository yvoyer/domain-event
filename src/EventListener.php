<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent;

interface EventListener
{
    /**
     * Key value map, where key is the event full class name and the map is the method
     * to call when the event is triggered.
     *
     * ie.
     * array(
     *     "Full\Path\To\Event" => 'onEvent',
     * )
     *
     * @return array
     */
    public function listensTo(): array;
}
