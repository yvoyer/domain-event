<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Messaging\Results;

use Star\Component\DomainEvent\Messaging\Query;
use Webmozart\Assert\Assert;

abstract class ObjectQuery implements Query
{
    /**
     * @var object
     */
    private $result;

    abstract protected function getObjectType(): string;

    /**
     * @param mixed $result
     */
    final public function __invoke($result): void
    {
        /**
         * @var class-string<object> $class
         */
        $class = $this->getObjectType();
        Assert::isInstanceOf(
            $result,
            $class,
            'Query "' . static::class . '" expected an instance of "%2$s". Got: "%s".'
        );
        $this->result = $result;
    }

    public function getResult()
    {
        $type = $this->getObjectType();
        if (! $this->result instanceof $type) {
            throw new \RuntimeException('Query "' . static::class . '" was never invoked.');
        }

        return $this->result;
    }
}
