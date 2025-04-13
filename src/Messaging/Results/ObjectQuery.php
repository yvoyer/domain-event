<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Messaging\Results;

use Assert\Assertion;
use Star\Component\DomainEvent\Messaging\Query;

/**
 * @deprecated This class will be removed in 3.0, stop usage and only implement interface.
 * @see https://github.com/yvoyer/domain-event/issues/50
 */
abstract class ObjectQuery implements Query
{
    /**
     * @var object
     */
    private $result;

    /**
     * @deprecated This method along with the class will be removed in 3.0.
     */
    abstract protected function getObjectType(): string;

    /**
     * @param object $result
     * @return void
     * @throws \Assert\AssertionFailedException
     */
    final public function __invoke($result): void
    {
        /**
         * @var class-string<object> $class
         */
        $class = $this->getObjectType();
        Assertion::isInstanceOf(
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
