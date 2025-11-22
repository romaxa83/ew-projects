<?php

namespace App\GraphQL\Queries\BackOffice\Content\OurCases;

use App\GraphQL\Queries\Common\Content\OurCase\BaseOurCaseCategoriesQuery;
use App\Permissions\Content\OurCaseCategories\OurCaseCategoryListPermission;
use GraphQL\Type\Definition\Type;

class OurCaseCategoriesQuery extends BaseOurCaseCategoriesQuery
{
    public const PERMISSION = OurCaseCategoryListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return parent::args() + [
                'id' => [
                    'type' => Type::id(),
                ],
                'slug' => [
                    'type' => Type::string(),
                ],
                'active' => [
                    'type' => Type::boolean(),
                ],
            ];
    }
}
