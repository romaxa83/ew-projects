<?php

namespace App\GraphQL\Mutations\BackOffice\Dealers;

use App\Dto\Dealers\DealerDto;
use App\GraphQL\InputTypes\Dealers\DealerInput;
use App\GraphQL\Types\Dealers\DealerType;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Permissions\Dealers\DealerCreatePermission;
use App\Rules\Companies\CompanyRegisterRule;
use App\Rules\MemberUniqueEmailRule;
use App\Services\Dealers\DealerService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;
use Illuminate\Validation\Rule;

class CreateMutation extends BaseMutation
{
    public const NAME = 'dealerCreate';
    public const PERMISSION = DealerCreatePermission::KEY;

    public function __construct(
        protected DealerService $service,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'input' => DealerInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return DealerType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Dealer
    {
        $dto = DealerDto::byArgs($args['input']);

        $model = makeTransaction(
            fn(): Dealer => $this->service->create($dto)
        );

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.company_id' => [
                'required',
                'int',
                Rule::exists(Company::class, 'id'),
                new CompanyRegisterRule($args)
            ],
            'input.email' => ['required', 'string', 'email:filter', new MemberUniqueEmailRule()],
            'input.name' => ['required', 'string'],
        ];
    }
}
