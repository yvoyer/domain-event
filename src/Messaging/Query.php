<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Messaging;

interface Query extends Message
{
    public function __invoke($result): void;

    /**
     * @return mixed
     */
    public function getResult();
}
