<?php


namespace App\Permissions\Saas\Translations;


use App\Permissions\BasePermission;

class TranslationCreate extends BasePermission
{
    public const KEY = 'translation.create';

    public function getName(): string
    {
        return __('permissions.translation.grants.create');
    }

    public function getPosition(): int
    {
        return 20;
    }
}
