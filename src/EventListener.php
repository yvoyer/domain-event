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
     * to call when the event is triggered. When providing an array of method, the numeric
     * key is the priority of the method call.
     *
     * Note: 2 listener CANNOT have the same priority. When this conflict happens, an
     * exception will occur. The priorities need to be unique to avoid conflicts across ALL
     * listeners in your application for each event.
     *
     * Example:
     * [
     *     "Full\Path\To\Event" => 'onEvent', // Default priority of 0
     *     "Full\Path\To\Another\Event" => [
     *         100 => 'doFirstOnEvent', // Highest priority are executed first
     *         0 => 'doSecond',
     *         -100 => 'doLast', // Lowest priority are last
     *     ]
     * ]
     *
     * @return string[]
     */
    public function listensTo(): array;
}
