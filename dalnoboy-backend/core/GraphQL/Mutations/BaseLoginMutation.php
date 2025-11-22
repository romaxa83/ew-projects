<?php

namespace Core\GraphQL\Mutations;

use App\GraphQL\Types\NonNullType;
use Closure;
use Core\Enums\Messages\AuthorizationMessageEnum;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseLoginMutation extends BaseMutation
{
    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->getAuthGuard()->guest();
    }

    public function getAuthorizationMessage(): string
    {
        return AuthorizationMessageEnum::AUTHORIZED;
    }

    public function args(): array
    {
        return [
            'username' => [
                'type' => NonNullType::string(),
            ],
            'password' => [
                'type' => NonNullType::string(),
            ]
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): array
    {
        return $this->passportService->auth($args['username'], $args['password']);
    }

    protected function rules(array $args = []): array
    {
        return [
            'username' => [
                'required',
                'email'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                $this->loginRule($args)
            ],
        ];
    }

    abstract protected function loginRule(array $args): Rule;
}
