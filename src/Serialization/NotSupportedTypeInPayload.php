<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use InvalidArgumentException;
use Star\Component\DomainEvent\Serialization\Transformation\PropertyValueTransformer;
use function get_class;
use function gettype;
use function implode;
use function is_object;
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
        if (is_object($value)) {
            $type = sprintf('object(%s)', get_class($value));
        }

        parent::__construct(
            sprintf(
                'Payload do not support having a value of type "%s" as attribute "%s", ' .
                'only "%s" are supported. You may register a "%s" to support your value.',
                $type,
                $attribute,
                implode('|', ['int', 'string', 'float', 'bool', 'SerializableAttribute']),
                PropertyValueTransformer::class
            )
        );
    }
}
