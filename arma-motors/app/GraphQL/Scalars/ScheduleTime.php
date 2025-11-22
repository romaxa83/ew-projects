<?php

namespace App\GraphQL\Scalars;

use GraphQL\Type\Definition\ScalarType;

class ScheduleTime extends ScalarType
{
    public function serialize($value): null|array
    {
        if(isset($value['from']) && isset($value['to'])) {
            return [
                'from' => $value['from'],
                'to' => $value['to']
            ];
        }

        return null;
    }

    /**
     * Parse a externally provided variable value into a Carbon instance.
     *
     *
     * @throws \GraphQL\Error\Error
     */
    public function parseValue($value)
    {
        return $value;
    }

    /**
     * This always throws, as the Upload scalar must be used with a multipart form request.
     *
     * @param  \GraphQL\Language\AST\Node  $valueNode
     * @param  mixed[]|null  $variables
     *
     * @throws \GraphQL\Error\Error
     */
    public function parseLiteral($valueNode, array $variables = null): void
    {}
}
