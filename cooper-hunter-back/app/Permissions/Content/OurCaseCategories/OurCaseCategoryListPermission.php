<?php

namespace App\Permissions\Content\OurCaseCategories;

use Core\Permissions\BasePermission;

class OurCaseCategoryListPermission extends BasePermission
{
    public const KEY = 'our_case_category.list';

    public function getName(): string
    {
        return __('permissions.content.our_case_category.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
