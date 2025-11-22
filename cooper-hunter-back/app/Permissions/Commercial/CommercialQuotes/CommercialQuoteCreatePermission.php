<?php

namespace App\Permissions\Commercial\CommercialQuotes;

use Core\Permissions\BasePermission;

class CommercialQuoteCreatePermission extends BasePermission
{
    public const KEY = CommercialQuotePermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.commercial_quote.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
