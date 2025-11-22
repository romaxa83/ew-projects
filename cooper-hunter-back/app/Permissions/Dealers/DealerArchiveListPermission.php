<?php

namespace App\Permissions\Dealers;

use Core\Permissions\BasePermission;

class DealerArchiveListPermission extends BasePermission
{
    public const KEY = 'dealer.list-archive';

    public function getName(): string
    {
        return __('permissions.dealer.grants.list-archive');
    }

    public function getPosition(): int
    {
        return 4;
    }
}

