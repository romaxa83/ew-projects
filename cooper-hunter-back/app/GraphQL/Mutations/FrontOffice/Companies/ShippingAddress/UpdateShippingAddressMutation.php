<?php

namespace App\GraphQL\Mutations\FrontOffice\Companies\ShippingAddress;

use App\Dto\Companies\ShippingAddressDto;
use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\InputTypes\Companies\ShippingAddressInput;
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

class UpdateShippingAddressMutation extends BaseMutation
{
    public const NAME = 'companyShippingAddressUpdate';
    public const PERMISSION = CompanyShippingAddressUpdatePermission::KEY;

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
                'description' => "CompanyShippingAddressType ID"
            ],
            'shipping_address' => [
                'type' => ShippingAddressInput::type(),
                'rules' => ['required', 'array']
            ],
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
        /** @var ShippingAddress $address */
        $address = $this->repo->getBy('id', $args['id']);
        $dto = ShippingAddressDto::byArgs($args['shipping_address']);

        $address = makeTransaction(
            fn() => $this->service->update($dto, $address)
        );

        event(new CreateOrUpdateCompanyEvent($address->company));

        return $address;
    }
}


