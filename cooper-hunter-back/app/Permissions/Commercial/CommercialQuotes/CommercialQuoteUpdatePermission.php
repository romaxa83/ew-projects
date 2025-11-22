<?php

namespace App\Permissions\Commercial\CommercialQuotes;

use Core\Permissions\BasePermission;

class CommercialQuoteUpdatePermission extends BasePermission
{
    public const KEY = CommercialQuotePermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.commercial_quote.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
