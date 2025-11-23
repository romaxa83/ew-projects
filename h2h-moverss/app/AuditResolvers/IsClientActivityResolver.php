<?php

namespace App\AuditResolvers;

use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class IsClientActivityResolver implements Resolver
{
    public static function resolve(Auditable $auditable = null)
    {
        $pattern = '/^customer\/order\/([a-zA-Z0-9]+)\/inventories\/save$/';

        if(preg_match($pattern, Request::path())){
            return true;
        }

        return false;
    }
}

