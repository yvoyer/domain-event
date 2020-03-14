<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Messaging\Results;

use Star\Component\DomainEvent\Messaging\Query;
use Webmozart\Assert\Assert;

abstract class CollectionQuery implements Query
{
    /**
     * @var mixed[]
     */
    private $result = [];

    /**
     * @param mixed[] $result
     */
    final public function __invoke($result): void
    {
        $this->validateResult($result);
        Assert::isArray(
            $result,
            'Query "' . static::class . '" expected an array, got: "%s".'
        );
        $this->result = $result;
    }

    /**
     * @param mixed $result
     */
    protected function validateResult($result): void
    {
    }

    /**
     * @return mixed[]
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
