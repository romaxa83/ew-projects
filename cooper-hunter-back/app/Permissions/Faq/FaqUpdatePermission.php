<?php

namespace App\Permissions\Faq;

use Core\Permissions\BasePermission;

class FaqUpdatePermission extends BasePermission
{
    public const KEY = 'faq.update';

    public function getName(): string
    {
        return __('permissions.faq.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
