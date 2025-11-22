<?php

namespace App\Http\Controllers\V1\Saas\GPS;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\GPS\History\HistoryFilterRequest;
use App\Http\Resources\Saas\GPS\History\HistoryResource;
use App\Models\GPS\History;
use App\Models\Saas\GPS\Device;
use App\Permissions\Saas\GPS\History\HistoryList;
use App\Repositories\Saas\GPS\HistoryRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HistoryController extends ApiController
{
    protected HistoryRepository $repo;

    public function __construct(
        HistoryRepository $repo
    )
    {
        parent::__construct();
        $this->repo = $repo;
    }

    /**
     * @param HistoryFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/v1/saas/gps/history", tags={"GPS history for device"},
     *     summary="Returns history list",
     *     operationId="history list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort by field - received_at", required=false,
     *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
     *     ),
     *     @OA\Parameter(name="id", in="query", description="ID", required=false,
     *           @OA\Schema(type="integer", example="1")
     *      ),
     *     @OA\Parameter(name="event_type", in="query", description="Event type", required=false,
     *            @OA\Schema(type="string", example="active", enum={"driving", "idle", "long_idle", "engine_off", "change_driver"})
     *     ),
     *     @OA\Parameter(name="device_id", in="query", description="Device ID", required=false,
     *           @OA\Schema(type="integer", example="5")
     *      ),
     *     @OA\Parameter(name="truck_id", in="query", description="Truck ID", required=false,
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer ID", required=false,
     *           @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="Driver ID", required=false,
     *            @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter (name="date_from", in="query", description="Date from", required=false,
     *          @OA\Schema (type="string", example="2023-08-22", description="format - Y-m-d")
     *      ),
     *      @OA\Parameter (name="date_to", in="query", description="Date to", required=false,
     *           @OA\Schema(type="string", example="2023-08-22", description="format - Y-m-d")
     *      ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryDevicePaginatedResource")
     *     ),
     * )
     */
    public function index(HistoryFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize(HistoryList::KEY);

        $model = History::filter($request->validated())
            ->with([
                'device',
                'trailer',
                'truck',
                'alerts',
                'alerts.trailer',
                'alerts.truck',
            ])
            ->orderBy('received_at', $request['order_type'] ?? 'desc')
            ->paginate(
                $request->getPerPage(),
                ['*'],
                'page',
                $request->getPage()
            );

        return HistoryResource::collection($model);
    }
}

