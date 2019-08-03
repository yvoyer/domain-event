<?php declare(strict_types=1);
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
final class UserWasRegistered implements DomainEvent
{
    private $blogName = 'My blog name';

    /**
     * @return string
     */
    public function blogName(): string
    {
        return $this->blogName;
    }
}
