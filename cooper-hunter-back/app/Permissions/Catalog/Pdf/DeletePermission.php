<?php

namespace App\Permissions\Catalog\Pdf;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = PdfPermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.catalog.pdf.grants.delete');
    }

    public function getPosition(): int
    {
        return 53;
    }
}

