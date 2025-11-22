<?php

namespace App\Permissions\Content\OurCaseCategories;

use Core\Permissions\BasePermission;

class OurCaseCategoryCreatePermission extends BasePermission
{
    public const KEY = 'our_case_category.create';

    public function getName(): string
    {
        return __('permissions.content.our_case_category.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
