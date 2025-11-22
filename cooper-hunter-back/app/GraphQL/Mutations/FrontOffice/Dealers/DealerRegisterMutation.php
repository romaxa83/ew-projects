<?php

namespace App\GraphQL\Mutations\FrontOffice\Dealers;

use App\Dto\Dealers\DealerRegisterDto;
use App\Exceptions\Technicians\TechnicianLicenseIsMissingException;
use App\GraphQL\Types\Members\MemberLoginType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Repositories\Companies\CompanyRepository;
use App\Repositories\Dealers\DealerRepository;
use App\Rules\Dealers\DealerCheckEmailRule;
use App\Rules\MemberUniqueEmailRule;
use App\Rules\PasswordRule;
use App\Services\Auth\DealerPassportService;
use App\Services\Dealers\DealerService;
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

class DealerRegisterMutation extends BaseMutation
{
    use CanRememberMe;
    use ValidateOnlyRequest;

    public const NAME = 'dealerRegister';

    public function __construct(
        protected DealerService $service,
        protected CompanyRepository $repoCompany,
        protected DealerPassportService $passportService
    ) {
        $this->setDealerGuard();
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
        return MemberLoginType::nonNullType();
    }

    public function args(): array
    {
        return [
                'code' => NonNullType::string(),
                'email' => NonNullType::string(),
                'password' => NonNullType::string(),
                'password_confirmation' => NonNullType::string(),
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
    ): array
    {
        $dto = DealerRegisterDto::byArgs($args);
        /** @var $company Company */
        $company = $this->repoCompany->getBy('code', $args['code']);

        makeTransaction(
            fn() => $this->service->register($dto, $company)
        );

        $this->setRefreshTokenTtl($args['remember_me']);

        return $this->passportService->auth(
                $dto->email,
                $dto->password
            ) + ['member_guard' => $this->guard];
    }

    protected function rules(array $args = []): array
    {
        return [
                'code' => ['required', 'string', Rule::exists(Company::class, 'code')],
                'email' => ['required', 'string', 'email:filter', new MemberUniqueEmailRule(), new DealerCheckEmailRule($args)],
                'password' => ['required', 'string', new PasswordRule(), 'confirmed']
            ] + $this->rememberMeRule();
    }
}
