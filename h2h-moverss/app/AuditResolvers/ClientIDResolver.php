<?php

namespace App\AuditResolvers;

use App\Models\Client;
use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class ClientIDResolver implements Resolver
{
    public static function resolve(Auditable $auditable = null)
    {
        $pattern = '/^customer\/order\/([a-zA-Z0-9]+)\/inventories\/save$/';

        if (method_exists($auditable, 'client') && $auditable->client)
            return $auditable->client->id;
        if ($auditable instanceof Client)
            return $auditable->id;
        if(preg_match($pattern, Request::path())){
            return $auditable?->order?->client->id;
        }

        return null;
    }
}
