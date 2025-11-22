<?php

namespace App\GraphQL\Queries\Common\Companies;

use App\GraphQL\Types\Companies\ShippingAddressForListType;
use App\Models\Companies\Company;
use App\Permissions\Companies\ShippingAddress\CompanyShippingAddressListPermission;
use App\Repositories\Companies\ShippingAddressRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseShippingAddressListQuery extends BaseQuery
{
    public const NAME = 'companyShippingAddressList';
    public const PERMISSION = CompanyShippingAddressListPermission::KEY;

    public function __construct(protected ShippingAddressRepository $repo)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return [
            'company_id' => [
                'type' => Type::id(),
                'rules' => ['nullable', 'int', Rule::exists(Company::class, 'id')],
            ],
            'active' => [
                'type' => Type::boolean()
            ]
        ];
    }

    public function type(): Type
    {
        return ShippingAddressForListType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->getAllObj(
            $fields->getSelect(),
            [],
            $args,
            ['name' => 'asc']
        );
    }
}
