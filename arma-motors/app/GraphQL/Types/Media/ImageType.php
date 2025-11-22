<?php

namespace App\GraphQL\Types\Media;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ImageType
{
    public function sizes($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return $rootValue;
    }
}

