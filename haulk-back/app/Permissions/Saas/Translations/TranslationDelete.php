<?php


namespace App\Permissions\Saas\Translations;


use App\Permissions\BasePermission;

class TranslationDelete extends BasePermission
{
    public const KEY = 'translation.delete';

    public function getName(): string
    {
        return __('permissions.translation.grants.delete');
    }

    public function getPosition(): int
    {
        return 50;
    }
}
