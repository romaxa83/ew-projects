<?php

namespace App\Services\Orders;

use App\Contracts\Roles\HasGuardUser;
use App\Exceptions\Orders\OrdersHaveThisDeliveryTypeException;
use App\Models\BaseHasTranslation;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Services\BaseCrudDictionaryService;
use Illuminate\Database\Eloquent\Collection;

class OrderDeliveryTypeService extends BaseCrudDictionaryService
{
    public function getList(array $args, HasGuardUser $authUser): ?Collection
    {
        return OrderDeliveryType::forGuard($authUser)
            ->filter($args)
            ->latest('sort')
            ->get();
    }

    protected function getModel(): string
    {
        return OrderDeliveryType::class;
    }

    protected function checkOffModel(BaseHasTranslation|OrderDeliveryType $model): void
    {
        if (!$model->shippings()
            ->exists()) {
            return;
        }

        throw new OrdersHaveThisDeliveryTypeException();
    }
}
