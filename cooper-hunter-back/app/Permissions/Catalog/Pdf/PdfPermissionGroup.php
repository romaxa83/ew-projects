<?php

namespace App\Permissions\Catalog\Pdf;

use Core\Permissions\BasePermissionGroup;

class PdfPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.pdf';

    public function getName(): string
    {
        return __('permissions.catalog.pdf.group');
    }

    public function getPosition(): int
    {
        return 100;
    }
}
