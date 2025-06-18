<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Messaging\Results;

use Assert\Assertion;
use Star\Component\DomainEvent\Messaging\Query;
use function sprintf;
use function trigger_error;

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
     * @deprecated This method along with the class will be removed in 3.0.
     */
    final public function __invoke($result): void
    {
        @trigger_error(
            sprintf(
                'Abstract query "%s" will be removed in 3.0. No replacements provided.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
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

    /**
     * @deprecated This method along with the class will be removed in 3.0.
     */
    public function getResult()
    {
        @trigger_error(
            sprintf(
                'Abstract query "%s" will be removed in 3.0. No replacements provided.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $type = $this->getObjectType();
        if (! $this->result instanceof $type) {
            throw new \RuntimeException('Query "' . static::class . '" was never invoked.');
        }

        return $this->result;
    }
}
