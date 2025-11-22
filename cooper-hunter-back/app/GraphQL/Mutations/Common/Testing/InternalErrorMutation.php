<?php

namespace App\GraphQL\Mutations\Common\Testing;

use App\GraphQL\Types\Messages\ResponseMessageType;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use RuntimeException;

class InternalErrorMutation extends BaseMutation
{
    public const NAME = 'internalError';
    public const DESCRIPTION = 'Always throws an internal server error';

    public function type(): Type
    {
        return ResponseMessageType::type();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): mixed
    {
        throw new RuntimeException('Internal server error');
    }
}