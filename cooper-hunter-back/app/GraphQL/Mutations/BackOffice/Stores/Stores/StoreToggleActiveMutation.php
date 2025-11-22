<?php

namespace App\GraphQL\Mutations\BackOffice\Stores\Stores;

use App\GraphQL\Types\Stores\StoreType;
use App\Models\BaseModel;
use App\Models\Stores\Store;
use App\Permissions\Stores\Stores\StoreUpdatePermission;
use Core\GraphQL\Mutations\BaseToggleActiveMutation;
use GraphQL\Type\Definition\Type;

class StoreToggleActiveMutation extends BaseToggleActiveMutation
{
    public const NAME = 'storeToggleActive';
    public const PERMISSION = StoreUpdatePermission::KEY;

    public function type(): Type
    {
        return StoreType::nonNullType();
    }

    protected function model(): BaseModel|string
    {
        return Store::class;
    }
}
