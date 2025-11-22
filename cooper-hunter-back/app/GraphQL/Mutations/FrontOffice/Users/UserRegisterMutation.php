<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\Dto\Users\UserDto;
use App\GraphQL\Types\Members\MemberLoginType;
use App\GraphQL\Types\NonNullType;
use App\Rules\MemberUniqueEmailRule;
use App\Rules\MemberUniquePhoneRule;
use App\Rules\NameRule;
use App\Rules\PasswordRule;
use App\Services\Auth\UserPassportService;
use App\Services\Users\UserService;
use App\Traits\Auth\CanRememberMe;
use App\Traits\ValidateOnlyRequest;
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
    use ValidateOnlyRequest;

    public const NAME = 'userRegister';

    public function __construct(
        private UserService $userService,
        private UserPassportService $userPassportService
    ) {
        $this->setUserGuard();
    }

    public function authorize(
        $root,
        array $args,
        $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->getAuthGuard()->guest();
    }

    public function getAuthorizationMessage(): string
    {
        return AuthorizationMessageEnum::AUTHORIZED;
    }

    public function type(): Type
    {
        return MemberLoginType::nonNullType();
    }

    public function args(): array
    {
        return [
                'first_name' => NonNullType::string(),
                'last_name' => NonNullType::string(),
                'email' => NonNullType::string(),
                'phone' => Type::string(),
                'password' => NonNullType::string(),
                'password_confirmation' => NonNullType::string(),
                'sms_access_token' => [
                    'type' => Type::string(),
                    'deprecationReason' => 'Подтверждение телефона более недоступно при регистрации. Только через ЛА'
                ]
            ]
            + $this->rememberMeArg()
            + $this->validateOnlyArg();
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
    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): array {
        $dto = UserDto::byArgs($args);

        makeTransaction(
            fn() => $this->userService->register($dto)
        );

        $this->setRefreshTokenTtl($args['remember_me']);

        return $this->userPassportService->auth(
                $dto->getEmail(),
                $dto->getPassword()
            ) + ['member_guard' => $this->guard];
    }

    protected function rules(array $args = []): array
    {
        return [
                'first_name' => [
                    'required',
                    'string',
                    new NameRule('first_name')
                ],
                'last_name' => [
                    'required',
                    'string',
                    new NameRule('last_name')
                ],
                'email' => [
                    'required',
                    'string',
                    'email:filter',
                    new MemberUniqueEmailRule()
                ],
                'phone' => ['nullable', 'string', new MemberUniquePhoneRule()],
                'password' => [
                    'required',
                    'string',
                    new PasswordRule(),
                    'confirmed'
                ],
            ] + $this->rememberMeRule();
    }
}
