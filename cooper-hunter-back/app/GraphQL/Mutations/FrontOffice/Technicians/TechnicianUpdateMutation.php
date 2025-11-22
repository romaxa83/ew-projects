<?php

namespace App\GraphQL\Mutations\FrontOffice\Technicians;

use App\Dto\Technicians\TechnicianDto;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Technicians\TechnicianProfileType;
use App\Models\Locations\Country;
use App\Models\Technicians\Technician;
use App\Permissions\Technicians\TechnicianUpdatePermission;
use App\Rules\ExistsLanguages;
use App\Rules\MemberUniqueEmailRule;
use App\Rules\MemberUniquePhoneRule;
use App\Rules\NameRule;
use App\Services\Technicians\TechnicianService;
use App\Traits\Auth\CanResetPassword;
use App\Traits\Auth\SmsConfirmable;
use App\Traits\ValidateOnlyRequest;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TechnicianUpdateMutation extends BaseMutation
{
    use CanResetPassword;
    use SmsConfirmable;
    use ValidateOnlyRequest;

    public const NAME = 'technicianUpdate';
    public const PERMISSION = TechnicianUpdatePermission::KEY;

    public function __construct(protected TechnicianService $service)
    {
        $this->setTechnicianGuard();
    }

    public function type(): Type
    {
        return TechnicianProfileType::type();
    }

    public function args(): array
    {
        return [
                'state_id' => NonNullType::id(),
                'country_code' => Type::string(),
                'license' => NonNullType::string(),
                'first_name' => NonNullType::string(),
                'last_name' => NonNullType::string(),
                'email' => NonNullType::string(),
                'phone' => Type::string(),
                'lang' => Type::string(),
            ]
            + $this->smsAccessTokenArg()
            + $this->passwordArg()
            + $this->validateOnlyArg();
    }

    /** @throws Throwable */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Technician
    {
        return makeTransaction(
            fn() => $this->service->update(
                $this->user(),
                TechnicianDto::byArgs($args)
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            [
                'country_code' => ['required', 'string', Rule::exists(Country::TABLE, 'country_code')],
                'first_name' => ['required', 'string', new NameRule('first_name')],
                'last_name' => ['required', 'string', new NameRule('last_name')],
                'email' => [
                    'required',
                    'string',
                    'email:filter',
                    MemberUniqueEmailRule::ignoreTechnician($this->authId())
                ],
                'phone' => ['nullable', 'string', MemberUniquePhoneRule::ignoreTechnician($this->authId())],
                'lang' => ['nullable', 'string', 'min:2', 'max:3', new ExistsLanguages()],
            ] + $this->passwordRule()
        );
    }
}
