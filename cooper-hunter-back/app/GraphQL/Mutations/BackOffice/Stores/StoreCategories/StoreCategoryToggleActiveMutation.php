<?php

namespace App\GraphQL\Mutations\BackOffice\Stores\StoreCategories;

use App\GraphQL\Types\Stores\StoreCategoryType;
use App\Models\BaseModel;
use App\Models\Stores\StoreCategory;
use App\Permissions\Stores\StoreCategories\StoreCategoryUpdatePermission;
use Core\GraphQL\Mutations\BaseToggleActiveMutation;
use GraphQL\Type\Definition\Type;

class StoreCategoryToggleActiveMutation extends BaseToggleActiveMutation
{
    public const NAME = 'storeCategoryToggleActive';
    public const PERMISSION = StoreCategoryUpdatePermission::KEY;

    public function type(): Type
    {
        return StoreCategoryType::nonNullType();
    }

    protected function model(): BaseModel|string
    {
        return StoreCategory::class;
    }
}
