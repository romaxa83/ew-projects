<?php

namespace App\AuditResolvers;

use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class DispatchTruckAtResolver implements Resolver
{

    public static function resolve(Auditable $auditable = null)
    {
        if(strpos(Request::path(), 'dispatch') !== false && Request::has('start_date')){
            return Request::get('start_date');
        }

        return null;
    }
}
