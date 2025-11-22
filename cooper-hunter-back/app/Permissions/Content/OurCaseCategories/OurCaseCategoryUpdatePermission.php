<?php

namespace App\Permissions\Content\OurCaseCategories;

use Core\Permissions\BasePermission;

class OurCaseCategoryUpdatePermission extends BasePermission
{
    public const KEY = 'our_case_category.update';

    public function getName(): string
    {
        return __('permissions.content.our_case_category.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
