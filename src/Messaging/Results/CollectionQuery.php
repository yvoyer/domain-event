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
     * @var array
     */
    private $result = [];

    final public function __invoke($result): void
    {
        $this->validateResult($result);
        Assert::isArray(
            $result,
            'Query "' . static::class . '" expected an array, got: "%s".'
        );
        $this->result = $result;
    }

    protected function validateResult($result): void
    {
    }

    public function getResult(): array
    {
        return $this->result;
    }
}
