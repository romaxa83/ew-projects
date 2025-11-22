<?php


namespace App\Permissions\Saas\Translations;


use App\Permissions\BasePermissionGroup;

class TranslationPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'translation';

    public function getName(): string
    {
        return __('permissions.translation.group');
    }
}
