<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Messaging;

interface Query extends Message
{
    /**
     * @param mixed $result
     */
    public function __invoke($result): void;

    /**
     * @return mixed
     * @deprecated Will be removed from the interface in 3.0.
     * No need to change anything unless you type hinted to Query and used getResult().
     * @see https://github.com/yvoyer/domain-event/issues/50
     */
    public function getResult();
}
