<?php

namespace Core\GraphQL\Mutations;

use Core\Services\Auth\AuthPassportService;
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

    abstract protected function getPassportService(): AuthPassportService;

    abstract protected function getGuard(): string;

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): array
    {
        return $this->getPassportService()->refreshToken($args['refresh_token']) + ['guard' => $this->getGuard()];
    }

    protected function rules(array $args = []): array
    {
        return [
            'refresh_token' => ['required', 'string'],
        ];
    }

}
