<?php

namespace App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories;

use App\GraphQL\Types\Content\OurCase\OurCaseCategoryType;
use App\Models\BaseModel;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Permissions\Content\OurCaseCategories\OurCaseCategoryUpdatePermission;
use Core\GraphQL\Mutations\BaseToggleActiveMutation;
use GraphQL\Type\Definition\Type;

class OurCaseCategoryToggleActiveMutation extends BaseToggleActiveMutation
{
    public const NAME = 'ourCaseCategoryToggleActive';
    public const PERMISSION = OurCaseCategoryUpdatePermission::KEY;

    public function type(): Type
    {
        return OurCaseCategoryType::nonNullType();
    }

    public function model(): BaseModel|string
    {
        return OurCaseCategory::class;
    }
}
