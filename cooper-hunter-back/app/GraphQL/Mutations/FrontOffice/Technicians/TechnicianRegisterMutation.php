<?php

namespace App\GraphQL\Mutations\FrontOffice\Technicians;

use App\Dto\Technicians\TechnicianDto;
use App\Exceptions\Technicians\TechnicianLicenseIsMissingException;
use App\GraphQL\Types\Members\MemberLoginType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Rules\MemberUniqueEmailRule;
use App\Rules\MemberUniquePhoneRule;
use App\Rules\NameRule;
use App\Rules\PasswordRule;
use App\Services\Auth\TechnicianPassportService;
use App\Services\Technicians\TechnicianService;
use App\Traits\Auth\CanRememberMe;
use App\Traits\ValidateOnlyRequest;
use Closure;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TechnicianRegisterMutation extends BaseMutation
{
    use CanRememberMe;
    use ValidateOnlyRequest;

    public const NAME = 'technicianRegister';

    public function __construct(
        private TechnicianService $technicianService,
        private TechnicianPassportService $technicianPassportService
    ) {
        $this->setTechnicianGuard();
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
                'country_code' => Type::string(),
                'state_id' => NonNullType::id(),
                'license' => NonNullType::string(),
                'first_name' => NonNullType::string(),
                'last_name' => NonNullType::string(),
                'email' => NonNullType::string(),
                'phone' => Type::string(),
                'password' => NonNullType::string(),
                'password_confirmation' => NonNullType::string(),
                'sms_access_token' => [
                    'type' => Type::string(),
                    'deprecationReason' => 'Подтверждение телефона более недоступно при регистрации. Только через ЛА'
                ],
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
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     *
     * @return array
     * @throws Throwable
     * @throws TechnicianLicenseIsMissingException
     */
    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): array {
        if (!isset($args['country_code'])) {
            $args['country_code'] = 'US';
        }

        $dto = TechnicianDto::byArgs($args);

        makeTransaction(
            fn() => $this->technicianService->register($dto)
        );

        $this->setRefreshTokenTtl($args['remember_me']);

        return $this->technicianPassportService->auth(
                $dto->getEmail(),
                $dto->getPassword()
            ) + ['member_guard' => $this->guard];
    }

    protected function rules(array $args = []): array
    {
        return [
                'country_code' => [
                    'required',
                    'string',
                    Rule::exists(Country::TABLE, 'country_code')
                ],
                'state_id' => [
                    'required',
                    'int',
                    Rule::exists(State::TABLE, 'id')
                ],
                'license' => ['required', 'string'],
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
                ]
            ] + $this->rememberMeRule();
    }
}
