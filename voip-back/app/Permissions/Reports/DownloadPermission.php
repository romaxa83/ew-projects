<?php

namespace App\Permissions\Reports;

use Core\Permissions\BasePermission;

class DownloadPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.download';

    public function getName(): string
    {
        return __('permissions.reports.grants.download');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
