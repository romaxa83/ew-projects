<?php

namespace App\Http\Controllers\Api\Logs;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Logs\IndexLogRequest;
use App\Http\Resources\Logs\LogResource;
use App\Models\Logs\Log;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LogsController extends ApiController
{

    /**
     * @OA\Get(path="/api/logs", tags={"Logs"}, summary="Get logs paginated list", operationId="Get logs data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="date_from", in="query", description="Date from filtering (M/D/Y H:M:S)", required=true,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="date_to", in="query", description="Date to filtering (M/D/Y H:M:S)", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="level_name", in="query", description="Log level names (DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY)", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(allOf={@OA\Schema(type="string")})
     *          )
     *     ),
     *     @OA\Parameter(name="message", in="query", description="Search string", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Orders per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/LogPaginatedResource")
     *     ),
     * )
     */

    /**
     * @param IndexLogRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexLogRequest $request): AnonymousResourceCollection
    {
        $logs = Log::query()
            ->filter($request->validated())
            ->getQuery()
            ->paginate($request->getPerPage(), ['*'], 'page', $request->getPage());

        return LogResource::collection($logs);
    }
}
