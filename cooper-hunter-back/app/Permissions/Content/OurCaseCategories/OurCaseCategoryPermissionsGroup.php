<?php

namespace App\Permissions\Content\OurCaseCategories;

use Core\Permissions\BasePermissionGroup;

class OurCaseCategoryPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'our_case_category';

    public function getName(): string
    {
        return __('permissions.content.our_case_category.group');
    }

    public function getPosition(): int
    {
        return 75;
    }
}
