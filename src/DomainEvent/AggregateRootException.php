<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

final class AggregateRootException extends \Exception
{
    /**
     * @param AggregateRoot $aggregate
     * @param string $method
     *
     * @return AggregateRootException
     */
    public static function missingMutationOnAggregate(AggregateRoot $aggregate, $method)
    {
        $class = get_class($aggregate);

        return new self("The mutation '{$method}' do not exists on aggregate '{$class}'.");
    }
}
