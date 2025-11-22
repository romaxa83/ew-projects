<?php

namespace Core\GraphQL\Mutations;

use App\Rules\LoginAdmin;
use App\Traits\Auth\CanRememberMe;
use Closure;
use Core\Enums\Messages\AuthorizationMessageEnum;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseLoginMutation extends BaseMutation
{
    use CanRememberMe;

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
                'username' => Type::nonNull(Type::string()),
                'password' => Type::nonNull(Type::string()),
            ] + $this->rememberMeArg();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): array
    {
        $this->setRefreshTokenTtl($args['remember_me']);

        return $this->passportService->auth($args['username'], $args['password']);
    }

    protected function rules(array $args = []): array
    {
        return [
                'username' => ['required', 'email'],
                'password' => ['required', 'string', 'min:8', new LoginAdmin($args)],
            ] + $this->rememberMeRule();
    }
}
