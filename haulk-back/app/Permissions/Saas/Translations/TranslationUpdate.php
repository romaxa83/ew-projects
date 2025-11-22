<?php


namespace App\Permissions\Saas\Translations;


use App\Permissions\BasePermission;

class TranslationUpdate extends BasePermission
{
    public const KEY = 'translation.update';

    public function getName(): string
    {
        return __('permissions.translation.grants.update');
    }

    public function getPosition(): int
    {
        return 40;
    }
}
