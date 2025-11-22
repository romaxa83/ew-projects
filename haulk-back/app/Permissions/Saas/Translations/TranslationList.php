<?php


namespace App\Permissions\Saas\Translations;


use App\Permissions\BasePermission;

class TranslationList extends BasePermission
{
    public const KEY = 'translation.list';

    public function getName(): string
    {
        return __('permissions.translation.grants.list');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
