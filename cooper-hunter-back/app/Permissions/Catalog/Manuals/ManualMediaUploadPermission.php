<?php

namespace App\Permissions\Catalog\Manuals;

use Core\Permissions\BasePermission;

class ManualMediaUploadPermission extends BasePermission
{
    public const KEY = ManualPermissionGroup::KEY . '.media_upload';

    public function getName(): string
    {
        return __('permissions.catalog.manual.grants.media_upload');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
