<?php

namespace App\Http\Middleware;

use App;
use App\Models\Admin\Admin;
use App\Models\Localization\Language;
use App\Models\User\User;
use Closure;
use Illuminate\Support\Facades\Request;

class Cache
{
    public function handle($request, Closure $next)
    {
        dd($request->all(), $request->input('variables'));



        return $next($request);
    }
}

