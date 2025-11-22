<?php

namespace App\Http\Middleware;

use App\Services\Forms\DraftService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DraftStore
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {

        if ($request->hasHeader(config('app.draft.header_key'))) {
            if (!($user = $request->user())) {
                abort(Response::HTTP_UNAUTHORIZED);
            }

            $path = $request->header(config('app.draft.header_key'));

            resolve(DraftService::class)->createOrUpdate($user, $path, $request->all());
        }

        return $next($request);
    }
}
