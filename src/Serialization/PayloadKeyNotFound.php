<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use InvalidArgumentException;

final class PayloadKeyNotFound extends InvalidArgumentException
{
}
