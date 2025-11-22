<?php

namespace App\Permissions\Catalog\Pdf;

use Core\Permissions\BasePermission;

class UploadPermission extends BasePermission
{
    public const KEY =  PdfPermissionGroup::KEY . '.upload';

    public function getName(): string
    {
        return __('permissions.catalog.pdf.grants.upload');
    }

    public function getPosition(): int
    {
        return 52;
    }
}
