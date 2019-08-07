<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
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
    public static function missingMutationOnAggregate(AggregateRoot $aggregate, string $method): self
    {
        $class = \get_class($aggregate);

        return new self("The mutation '{$method}' do not exists on aggregate '{$class}'.");
    }
}
