<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class DivisionIdResolver implements Resolver
{

    public static function resolve(Auditable $auditable = null)
    {
        $currentDivision = session('division');

        return $currentDivision['id'] ?? null;
    }
}
