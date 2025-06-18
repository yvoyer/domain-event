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
     * @deprecated Removed in 3.0
     */
    public function __invoke($result): void
    {
        @trigger_error(
            sprintf(
                'Abstract query "%s" will be removed in 3.0. No replacements provided.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        Assertion::scalar($result, 'Query "' . static::class . '" expected a scalar, got: "%s".');
        $this->result = $result;
    }

    /**
     * @deprecated Removed in 3.0
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
        if (\is_null($this->result)) {
            throw new \RuntimeException('Query "' . static::class . '" was never invoked.');
        }

        return $this->result;
    }
}
