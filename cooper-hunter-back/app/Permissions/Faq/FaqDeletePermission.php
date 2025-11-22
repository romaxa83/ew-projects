<?php

namespace App\Permissions\Faq;

use Core\Permissions\BasePermission;

class FaqDeletePermission extends BasePermission
{
    public const KEY = 'faq.delete';

    public function getName(): string
    {
        return __('permissions.faq.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
