<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Class Event
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Star\Component\DomainEvent
 */
abstract class Event extends BaseEvent
{
    /**
     * @return \DateTime
     */
    public abstract function createdAt();

    /**
     * @return string
     */
    public static function name()
    {
        throw new \RuntimeException('You need to define a name for this event by overriding this method.');
    }
}
