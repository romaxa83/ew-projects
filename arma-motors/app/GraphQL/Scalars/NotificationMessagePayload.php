<?php

namespace App\GraphQL\Scalars;

use GraphQL\Type\Definition\ScalarType;

class NotificationMessagePayload extends ScalarType
{
    public function serialize($value): null|array
    {
        if(isset($value['title']) && isset($value['body'])) {
            return [
                'title' => $value['title'],
                'body' => $value['body']
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

