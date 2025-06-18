<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent;

use Exception;
use function get_class;

final class AggregateRootException extends Exception
{
    public static function missingMutationOnAggregate(
        AggregateRoot $aggregate,
        string $method,
    ): self {
        $class = get_class($aggregate);

        return new self("The mutation '{$method}' do not exists on aggregate '{$class}'.");
    }
}
