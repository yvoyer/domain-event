<?php declare(strict_types=1);

namespace Star\Component\DomainEvent;

use RuntimeException;

final class DuplicatedListenerPriority extends RuntimeException
{
}
