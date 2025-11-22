<?php

namespace App\Permissions\Commercial\CommercialQuotes;

use Core\Permissions\BasePermissionGroup;

class CommercialQuotePermissionGroup extends BasePermissionGroup
{
    public const KEY = 'commercial_quote';

    public function getName(): string
    {
        return __('permissions.commercial_quote.group');
    }
}
