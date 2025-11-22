<?php

namespace App\GraphQL\Scalars;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\UploadedFile;

class ArrayData extends ScalarType
{
    public function serialize($value): array
    {
        return $value;
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
