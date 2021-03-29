<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use InvalidArgumentException;
use function get_class;
use function gettype;
use function implode;
use function sprintf;

final class NotSupportedTypeInPayload extends InvalidArgumentException
{
    /**
     * @param string $attribute
     * @param mixed $value
     */
    public function __construct(string $attribute, $value)
    {
        $type = sprintf('%s', gettype($value));
        if ($type === 'object') {
            $type = sprintf('object(%s)', get_class($value));
        }

        parent::__construct(
            sprintf(
                'Payload do not support having a value of type "%s" as attribute "%s", only "%s" are supported.',
                $type,
                $attribute,
                implode(', ', ['int', 'string', 'float', 'bool'])
            )
        );
    }
}
