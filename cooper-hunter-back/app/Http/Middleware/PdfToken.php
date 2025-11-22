<?php

namespace App\Http\Middleware;

use App\Dto\Utilities\Pdf\PdfDataDto;
use App\Traits\Utilities\HasPdfService;
use Closure;
use Illuminate\Http\Request;

class PdfToken
{
    use HasPdfService;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
logger_info("PdfToken middleware start");
        $token = $request->route()
            ->parameter('token');

        if (empty($token)) {
            return redirect(config('front_routes.not-found'));
        }

        $pdfData = $this->getPdfDataFromCache($token);

        if (empty($pdfData) || !$pdfData instanceof PdfDataDto) {
            return redirect(config('front_routes.not-found'));
        }

        $request->attributes->set('pdf', $pdfData);
        logger_info("PdfToken middleware end");
        return $next($request);
    }
}
