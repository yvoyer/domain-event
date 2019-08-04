<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Messaging\Results;

use Star\Component\DomainEvent\Messaging\Query;
use Webmozart\Assert\Assert;

abstract class ScalarQuery implements Query
{
    /**
     * @var bool|string|int|float|null
     */
    private $result;

    public function __invoke($result): void
    {
        Assert::scalar($result, 'Query "' . static::class . '" expected a scalar, got: "%s".');
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
