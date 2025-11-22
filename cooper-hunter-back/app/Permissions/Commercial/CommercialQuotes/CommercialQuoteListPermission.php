<?php

namespace App\Permissions\Commercial\CommercialQuotes;

use Core\Permissions\BasePermission;

class CommercialQuoteListPermission extends BasePermission
{
    public const KEY = CommercialQuotePermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.commercial_quote.grants.list');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
