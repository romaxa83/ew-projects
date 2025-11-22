<?php

namespace App\GraphQL\Mutations\FrontOffice\Companies;

use App\Dto\Companies\CompanyDto;
use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\InputTypes\Companies\CompanyInput;
use App\GraphQL\InputTypes\Companies\ContactInput;
use App\GraphQL\InputTypes\Companies\ShippingAddressInput;
use App\GraphQL\Types\Companies\CompanyType;
use App\GraphQL\Types\FileType;
use App\Models\Companies\Company;
use App\Services\Companies\CompanyService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CreateApplicationMutation extends BaseMutation
{
    public const NAME = 'companiesApplicationCreate';

    public function __construct(protected CompanyService $service)
    {}

    public function args(): array
    {
        return [
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
            ],
            'media' => [
                'type' => Type::listOf(FileType::Type()),
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
        $dto = CompanyDto::byArgs($args);

        $model = makeTransaction(
            fn(): Company => $this->service->create($dto)
        );

        event(new CreateOrUpdateCompanyEvent($model));

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
//            'company_info.phone' => ['required', 'string', Rule::unique(Company::TABLE, 'phone')],
//            'company_info.email' => ['required', 'string', 'email:filter', Rule::unique(Company::TABLE, 'email')],
//            'company_info.fax' => ['required', 'string', Rule::unique(Company::TABLE, 'fax')],
//            'email' => ['required', 'string', 'email:filter'],
        ];
    }
}

