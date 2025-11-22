<?php


namespace App\Http\Middleware;


use App\Traits\HasDownload;
use Closure;
use Illuminate\Http\Request;

class DownloadHash
{
    use HasDownload;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $hash = $request->route()
            ->parameter('hash');

        if (empty($hash)) {
            return redirect(config('routes.front.not_found'));
        }

        $dto = $this->getDownloadData($hash);

        if (empty($dto)) {
            return redirect(config('routes.front.not_found'));
        }

        $request->attributes->set('dto', $dto);

        return $next($request);
    }
}
