<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Request;

class SetCartHash
{
    public function handle($request, Closure $next)
    {
        if ($cartHash = Request::header('Cart-hash')) {
            config([ 'cms.orders.cart.hash' => $cartHash ]);
        }

        return $next($request);
    }
}
