<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\Dto\Users\UserDto;
use App\GraphQL\Types\Users\UserLoginType;
use App\Rules\NameRule;
use App\Rules\PasswordRule;
use App\Services\Auth\UserPassportService;
use App\Services\Users\UserService;
use App\Traits\Auth\CanRememberMe;
use Closure;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UserRegisterMutation extends BaseMutation
{
    use CanRememberMe;

    public const NAME = 'userRegister';

    public function __construct(private readonly UserService $userService, private readonly UserPassportService $userPassportService)
    {
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->getAuthGuard()->guest();
    }

    public function getAuthorizationMessage(): string
    {
        return AuthorizationMessageEnum::AUTHORIZED;
    }

    public function type(): Type
    {
        return UserLoginType::type();
    }

    public function args(): array
    {
        return [
                'first_name' => Type::nonNull(Type::string()),
                'last_name' => Type::nonNull(Type::string()),
                'middle_name' => Type::nonNull(Type::string()),
                'email' => Type::nonNull(Type::string()),
                'password' => Type::nonNull(Type::string()),
                'password_confirmation' => Type::nonNull(Type::string()),
            ] + $this->rememberMeArg();
    }

    public function validationErrorMessages(array $args = []): array
    {
        return [
            'email.unique' => __('validation.unique_email'),
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): array
    {
        $dto = UserDto::byArgs($args);

        make_transaction(
            fn() => $this->userService->register($dto)
        );

        $this->setRefreshTokenTtl($args['remember_me']);

        return $this->userPassportService->auth($dto->getEmail(), $dto->getPassword());
    }

    protected function rules(array $args = []): array
    {
        return [
                'first_name' => ['required', 'string', new NameRule('first_name')],
                'last_name' => ['required', 'string', new NameRule('last_name')],
                'middle_name' => ['required', 'string', new NameRule('middle_name')],
                'email' => ['required', 'string', 'email', 'unique:users,email'],
                'password' => ['required', 'string', new PasswordRule(), 'confirmed']
            ] + $this->rememberMeRule();
    }
}
