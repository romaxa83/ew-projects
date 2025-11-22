<?php

namespace App\GraphQL\Mutations\FrontOffice\Companies\ShippingAddress;

use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\ShippingAddress;
use App\Permissions\Companies\ShippingAddress\CompanyShippingAddressDeletePermission;
use App\Repositories\Companies\ShippingAddressRepository;
use App\Services\Companies\ShippingAddressService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DeleteShippingAddressMutation extends BaseMutation
{
    public const NAME = 'companyShippingAddressDelete';
    public const PERMISSION = CompanyShippingAddressDeletePermission::KEY;

    public function __construct(
        protected ShippingAddressService $service,
        protected ShippingAddressRepository $repo,
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(ShippingAddress::class, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
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
    ): bool
    {
        /** @var $model ShippingAddress */
        $model = $this->repo->getBy('id', $args['id']);

        event(new CreateOrUpdateCompanyEvent($model->company));

        return $this->service->delete($model);
    }
}

