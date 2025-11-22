<?php

namespace App\Permissions\Saas\GPS\History;

use App\Permissions\BasePermission;

class HistoryList extends BasePermission
{
    public const KEY = 'gps-history.list';

    public function getName(): string
    {
        return __('permissions.gps-history.grants.list');
    }

    public function getPosition(): int
    {
        return 10;
    }
}

