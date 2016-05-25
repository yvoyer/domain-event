<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Fixtures\Blog\Event;

use Star\Component\DomainEvent\DomainEvent;

/**
 * @author  Yannick Voyer (http://github.com/yvoyer)
 */
final class BlogWasCreated implements DomainEvent
{
    private $blogName = 'My blog name';

    /**
     * @return string
     */
    public function blogName()
    {
        return $this->blogName;
    }
}
