<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Request;

class ApiLocal
{
    public function handle($request, Closure $next)
    {
        // @todo временное решение, слетели перевод по валидации (ru), когда востановяться вернуть закоментированое
        App::setLocale('en');

        return $next($request);
    }
}

