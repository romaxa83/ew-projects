<?php

namespace App\GraphQL\Mutations\Common\Testing;

use App\GraphQL\Types\Messages\ResponseMessageType;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Foundation\Inspiring;
use Rebing\GraphQL\Support\SelectFields;

class TranslatedErrorMutation extends BaseMutation
{
    public const NAME = 'translatedError';
    public const DESCRIPTION = 'Always throws an translated server error';

    public function type(): Type
    {
        return ResponseMessageType::type();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): mixed
    {
        throw new TranslatedException(Inspiring::quote());
    }
}