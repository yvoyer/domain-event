<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Fixtures;

use Star\Component\DomainEvent\Event;

/**
 * Class MyCustomEvent
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Star\Component\DomainEvent\Fixtures
 */
final class MyCustomEvent extends Event
{
    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
        $this->createdAt = new \DateTime();
    }

    public function aggregateId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function createdAt()
    {
        return $this->createdAt;
    }

    public static function name()
    {
        return 'test';
    }
}
