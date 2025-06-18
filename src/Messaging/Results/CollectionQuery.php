<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Messaging\Results;

use Star\Component\DomainEvent\Messaging\Query;
use Webmozart\Assert\Assert;
use function sprintf;
use function trigger_error;

/**
 * @deprecated This class will be removed in 3.0, stop usage and only implement interface.
 * @see https://github.com/yvoyer/domain-event/issues/50
 */
abstract class CollectionQuery implements Query
{
    /**
     * @var mixed[]
     */
    private $result = [];

    /**
     * @param mixed[] $result
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
        $this->validateResult($result);
        Assert::isArray(
            $result,
            'Query "' . static::class . '" expected an array, got: "%s".'
        );
        $this->result = $result;
    }

    /**
     * @param mixed $result
     * @deprecated This method along with the class will be removed in 3.0.
     */
    protected function validateResult($result): void
    {
        @trigger_error(
            sprintf(
                'The method "%s::%s()" will be remove, along with its class in 3.0. ' .
                'Consider duplicating "__invoke" or implementing your own validation.',
                __CLASS__,
                __FUNCTION__
            ),
            E_USER_DEPRECATED
        );
    }

    /**
     * @return mixed[]
     * @deprecated This method along with the class will be removed in 3.0.
     */
    public function getResult(): array
    {
        @trigger_error(
            sprintf(
                'Abstract query "%s" will be removed in 3.0. No replacements provided.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        return $this->result;
    }
}
