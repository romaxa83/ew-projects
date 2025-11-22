<?php

namespace App\GraphQL\Scalars;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\UploadedFile;

class Location extends ScalarType
{
    public function serialize($value): array
    {

        if($value instanceof Point) {
            return [
                'lat' => $value->getLat(),
                'lon' => $value->getLng()
            ];
        }


        return [
            'lat' => null,
            'lon' => null
        ];
    }

    /**
     * Parse a externally provided variable value into a Carbon instance.
     *
     *
     * @throws \GraphQL\Error\Error
     */
    public function parseValue($value): UploadedFile
    {
        dd('2');
        if (! $value instanceof UploadedFile) {
            throw new Error(
                'Could not get uploaded file, be sure to conform to GraphQL multipart request specification: https://github.com/jaydenseric/graphql-multipart-request-spec Instead got: '.Utils::printSafe($value)
            );
        }

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
    {
        dd('3');
        throw new Error(
            '"Upload" cannot be hardcoded in a query. Be sure to conform to the GraphQL multipart request specification: https://github.com/jaydenseric/graphql-multipart-request-spec'
        );
    }
}
