<?php

namespace App\Permissions\Content\OurCaseCategories;

use Core\Permissions\BasePermission;

class OurCaseCategoryDeletePermission extends BasePermission
{
    public const KEY = 'our_case_category.delete';

    public function getName(): string
    {
        return __('permissions.content.our_case_category.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
