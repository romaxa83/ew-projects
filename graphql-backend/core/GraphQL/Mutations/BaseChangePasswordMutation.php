<?php

namespace Core\GraphQL\Mutations;

use App\Rules\MatchOldPassword;
use App\Rules\PasswordRule;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseChangePasswordMutation extends BaseMutation
{
    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->getAuthGuard()->check();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'current' => Type::nonNull(Type::string()),
            'password' => Type::nonNull(Type::string()),
            'password_confirmation' => Type::nonNull(Type::string()),
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->service->changePassword(
            $this->getAuthGuard()->user(),
            $args['password']
        );
    }

    protected function rules(array $args = []): array
    {
        if ($this->guest()) {
            return [];
        }

        return [
            'current' => ['required', 'string', new MatchOldPassword($this->guard)],
            'password' => ['required', 'string', new PasswordRule(), 'confirmed'],
        ];
    }
}
