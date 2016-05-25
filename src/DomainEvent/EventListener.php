<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

/**
 * @author  Yannick Voyer (http://github.com/yvoyer)
 */
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
    public function listensTo();
}
