<?php

namespace App\GraphQL\Mutations\BackOffice\Companies\ShippingAddress;

use App\GraphQL\Types\Companies\ShippingAddressType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\ShippingAddress;
use App\Permissions\Companies\ShippingAddress\CompanyShippingAddressUpdatePermission;
use App\Repositories\Companies\ShippingAddressRepository;
use App\Services\Companies\ShippingAddressService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ToggleActiveMutation extends BaseMutation
{
    public const NAME = 'companyShippingAddressToggleActive';
    public const PERMISSION = CompanyShippingAddressUpdatePermission::KEY;

    public function __construct(
        protected ShippingAddressService $service,
        protected ShippingAddressRepository $repo,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int',
                    Rule::exists(ShippingAddress::class, 'id')
                ],
                "description" => "ShippingAddressType ID"
            ]
        ];
    }

    public function type(): Type
    {
        return ShippingAddressType::nonNullType();
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
    ): ShippingAddress
    {
        /** @var $model ShippingAddress */
        $model = $this->repo->getBy('id', $args['id']);

        return $this->service->toggleActive($model);
    }
}
