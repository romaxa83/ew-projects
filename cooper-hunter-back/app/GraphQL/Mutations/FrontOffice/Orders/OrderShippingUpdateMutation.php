<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\InputTypes\Orders\OrderShippingInput;
use App\GraphQL\Mutations\Common\Orders\BaseOrderShippingUpdateMutation;
use App\Models\Technicians\Technician;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class OrderShippingUpdateMutation extends BaseOrderShippingUpdateMutation
{
    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->can(self::PERMISSION) && $this->can('isActive', Technician::class);
    }

    protected function setMutationGuard(): void
    {
        $this->setMemberGuard();
    }

    protected function getShippingInputType(): Type
    {
        return OrderShippingInput::nonNullType();
    }

    protected function notAvailableStatuses(): array
    {
        return [
            OrderStatusEnum::SHIPPED,
            OrderStatusEnum::CANCELED,
        ];
    }
}
