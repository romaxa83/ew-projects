<?php


namespace App\Permissions\Saas\Translations;


use App\Permissions\BasePermission;

class TranslationShow extends BasePermission
{
    public const KEY = 'translation.show';

    public function getName(): string
    {
        return __('permissions.translation.grants.show');
    }

    public function getPosition(): int
    {
        return 30;
    }
}
