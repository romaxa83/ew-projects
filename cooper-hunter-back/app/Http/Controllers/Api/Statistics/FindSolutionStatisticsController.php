<?php

namespace App\Http\Controllers\Api\Statistics;

use App\Http\Controllers\Controller;
use App\Services\Catalog\Solutions\SolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FindSolutionStatisticsController extends Controller
{
    /**
     * @hideFromAPIDocumentation
     */
    public function __invoke(Request $request, SolutionService $service)
    {
        try {
            $timestamp = Crypt::decryptString($request->token);

            abort_if(Carbon::createFromTimestamp($timestamp)->lt(now()), Response::HTTP_NOT_FOUND);

            parse_str($request->get('filters'), $args);

            return response()->streamDownload(
                static function () use ($service, $args) {
                    $service->getStatistics($args)->sendContent();
                },
                'solution-statistics-' . now()->format('m-d-Y') . '.xlsx'
            );
        } catch (Throwable) {
            abort(Response::HTTP_NOT_FOUND);
        }
    }
}
