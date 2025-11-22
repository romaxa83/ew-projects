<?php

namespace App\Http\Controllers\Api\GPS;

use App\Http\Controllers\ApiController;
use App\Http\Requests\GPS\AlertIndexRequest;
use App\Http\Requests\GPS\AlertListRequest;
use App\Http\Resources\GPS\AlertResource;
use App\Models\GPS\Alert;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertController extends ApiController
{
    /**
     * Get alerts list
     *
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\GET(
     *     path="/api/gps/alerts",
     *     tags={"Carrier GPS"},
     *     summary="Alerts pagination",
     *     operationId="AlertsPagination",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
     *     ),
     *     @OA\Parameter(name="truck_id", in="query", description="Truck id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="Driver id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="alert_type", in="query", description="Alert type", required=false,
     *          @OA\Schema(type="string", default="", enum={"speeding", "device_battery", "device_connection_lost", "device_connection_restored"})
     *     ),
     *     @OA\Parameter(name="vehicle_unit_number", in="query", description="vehicle unit number", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/GPSAlertPaginated")
     *     ),
     * )
     */
    public function index(AlertIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('gps alerts');

        $startFromTime = now()->subDays(config('gps.count_days_to_show_alerts') - 1)->startOfDay();

        $alerts = Alert::query()
            ->where('company_id', authUser()->getCompanyId())
            ->where('received_at', '>=', $startFromTime)
            ->filter($request->validated())
            ->orderBy('received_at', $request->order_type ?? 'desc')
            ->paginate(10);

        return AlertResource::collection($alerts);
    }

    /**
     * Get alerts list
     *
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\GET(
     *     path="/api/gps/alerts-list",
     *     tags={"Carrier GPS"},
     *     summary="Alerts list",
     *     operationId="AlertsList",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="date", in="query", description="Date", required=false,
     *          @OA\Schema(type="string", example="2023-08-22", description="format - Y-m-d")
     *     ),
     *     @OA\Parameter(name="truck_id", in="query", description="Truck id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="device_id", in="query", description="Device id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter (name="alert_type", in="query", description="Alert type", required=false,
     *           @OA\Schema(type="array", @OA\Items(
     *               type="string", enum={"speeding", "device_battery", "device_connection_lost", "device_connection_restored"}
     *           ))
     *      ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/GPSAlertList")
     *     ),
     * )
     */
    public function list(AlertListRequest $request): AnonymousResourceCollection
    {
        $this->authorize('gps alerts');

        $alerts = Alert::query()
            ->where('company_id', authUser()->getCompanyId())
            ->filter($request->validated())
            ->orderBy('received_at', $request->order_type ?? 'desc')
            ->get();

        return AlertResource::collection($alerts);
    }
}
