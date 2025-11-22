<?php

namespace App\Permissions\Faq;

use Core\Permissions\BasePermission;

class FaqCreatePermission extends BasePermission
{
    public const KEY = 'faq.create';

    public function getName(): string
    {
        return __('permissions.faq.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
