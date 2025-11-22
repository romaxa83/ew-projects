<?php

namespace Core\GraphQL\Mutations;

use App\GraphQL\Types\NonNullType;
use App\Rules\LoginAdmin;
use App\Traits\Auth\CanRememberMe;
use Closure;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\Services\Auth\AuthPassportService;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseLoginMutation extends BaseMutation
{
    use CanRememberMe;

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool
    {
        return $this->getAuthGuard()->guest();
    }

    public function getAuthorizationMessage(): string
    {
        return AuthorizationMessageEnum::AUTHORIZED;
    }

    public function args(): array
    {
        return [
                'username' => NonNullType::string(),
                'password' => NonNullType::string(),
            ] + $this->rememberMeArg();
    }

    /** @throws Throwable */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): array
    {
        $this->setRefreshTokenTtl($args['remember_me']);

        return $this->getPassportService()->auth($args['username'], $args['password']) + [
                'member_guard' => $this->guard
            ];
    }

    abstract protected function getPassportService(): AuthPassportService;

    protected function rules(array $args = []): array
    {
        return [
                'username' => ['required', 'email:filter'],
                'password' => ['required', 'string', 'min:8', new LoginAdmin($args)],
            ] + $this->rememberMeRule();
    }
}
