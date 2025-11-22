<?php

namespace App\GraphQL\Mutations\BackOffice\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\InputTypes\Orders\BackOffice\OrderShippingBackOfficeInput;
use App\GraphQL\Mutations\Common\Orders\BaseOrderShippingUpdateMutation;
use GraphQL\Type\Definition\Type;

class OrderShippingUpdateMutation extends BaseOrderShippingUpdateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }

    protected function getShippingInputType(): Type
    {
        return OrderShippingBackOfficeInput::nonNullType();
    }

    protected function notAvailableStatuses(): array
    {
        return [
            OrderStatusEnum::CANCELED,
        ];
    }
}
