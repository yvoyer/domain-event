<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Messaging\Results;

use Assert\Assertion;
use Star\Component\DomainEvent\Messaging\Query;

abstract class ScalarQuery implements Query
{
    /**
     * @var bool|string|int|float|null
     */
    private $result;

    /**
     * @param bool|float|int|string $result
     * @return void
     * @throws \Assert\AssertionFailedException
     */
    public function __invoke($result): void
    {
        Assertion::scalar($result, 'Query "' . static::class . '" expected a scalar, got: "%s".');
        $this->result = $result;
    }

    public function getResult()
    {
        if (\is_null($this->result)) {
            throw new \RuntimeException('Query "' . static::class . '" was never invoked.');
        }

        return $this->result;
    }
}
