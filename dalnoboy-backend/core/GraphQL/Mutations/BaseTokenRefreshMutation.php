<?php

namespace Core\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseTokenRefreshMutation extends BaseMutation
{
    public function args(): array
    {
        return [
            'refresh_token' => Type::nonNull(Type::string()),
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): array
    {
        return $this->passportService->refreshToken($args['refresh_token']);
    }

    protected function rules(array $args = []): array
    {
        return [
            'refresh_token' => ['required', 'string'],
        ];
    }

}
