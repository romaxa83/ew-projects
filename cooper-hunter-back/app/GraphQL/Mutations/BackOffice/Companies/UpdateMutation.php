<?php

namespace App\GraphQL\Mutations\BackOffice\Companies;

use App\Dto\Companies\CompanyDto;
use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\InputTypes\Companies\CompanyInput;
use App\GraphQL\InputTypes\Companies\ContactInput;
use App\GraphQL\InputTypes\Companies\ShippingAddressInput;
use App\GraphQL\Types\Companies\CompanyType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Company;
use App\Permissions\Companies\CompanyListPermission;
use App\Repositories\Companies\CompanyRepository;
use App\Services\Companies\CompanyService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UpdateMutation extends BaseMutation
{
    public const NAME = 'companyUpdate';
    public const PERMISSION = CompanyListPermission::KEY;

    public function __construct(
        protected CompanyService $service,
        protected CompanyRepository $repo,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Company::class, 'id')],
            ],
            'company_info' => [
                'type' => CompanyInput::type(),
                'rules' => ['required', 'array']
            ],
            'shipping_address' => [
                'type' => ShippingAddressInput::list(),
                'rules' => ['nullable', 'array']
            ],
            'contact_account' => [
                'type' => ContactInput::type(),
                'rules' => ['required', 'array']
            ],
            'contact_order' => [
                'type' => ContactInput::type(),
                'rules' => ['required', 'array']
            ]
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
        $dto = CompanyDto::byArgs($args);

        /** @var $model Company */
        $model = $this->repo->getBy('id', $args['id']);

        $model = makeTransaction(
            fn(): Company => $this->service->update($model, $dto)
        );

        event(new CreateOrUpdateCompanyEvent($model));

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'company_info.email' => [
                'required', 'string', 'email:filter',
                Rule::unique(Company::TABLE, 'email')->ignore($args['id'])
            ],
            'company_info.phone' => [
                'required', 'string',
                Rule::unique(Company::TABLE, 'phone')->ignore($args['id'])
            ],
            'company_info.fax' => [
                'required', 'string',
                Rule::unique(Company::TABLE, 'fax')->ignore($args['id'])
            ],
            'company_info.taxpayer_id' => [
                'required', 'string',
                Rule::unique(Company::TABLE, 'taxpayer_id')->ignore($args['id'])
            ],
//            'company_info.taxpayer_id' => [
//                'required', 'string',
//                Rule::unique(Company::TABLE, 'taxpayer_id')->ignore($args['id'])
//            ],
        ];
    }
}
