<?php

namespace App\GraphQL\Mutations\FrontOffice\Companies\ShippingAddress;

use App\Dto\Companies\ShippingAddressDto;
use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\InputTypes\Companies\ShippingAddressInput;
use App\GraphQL\Types\Companies\CompanyType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Company;
use App\Permissions\Companies\ShippingAddress\CompanyShippingAddressCreatePermission;
use App\Repositories\Companies\CompanyRepository;
use App\Services\Companies\ShippingAddressService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AddShippingAddressMutation extends BaseMutation
{
    public const NAME = 'companyShippingAddressAdd';
    public const PERMISSION = CompanyShippingAddressCreatePermission::KEY;

    public function __construct(
        protected ShippingAddressService $service,
        protected CompanyRepository $repoCompany,
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'company_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Company::class, 'id')],
                'description' => "CompanyType ID"
            ],
            'shipping_address' => [
                'type' => ShippingAddressInput::type(),
                'rules' => ['required', 'array']
            ],
        ];
    }

    public function type(): Type
    {
        return CompanyType::nonNullType();
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
    ): Company
    {
        /** @var Company $company */
        $company = $this->repoCompany->getBy('id', $args['company_id']);
        $dto = ShippingAddressDto::byArgs($args['shipping_address']);

        makeTransaction(
            fn() => $this->service->create($dto, $company)
        );

        event(new CreateOrUpdateCompanyEvent($company));

        return $company;
    }
}
