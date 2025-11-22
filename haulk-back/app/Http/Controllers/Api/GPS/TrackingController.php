<?php

namespace App\Http\Controllers\Api\GPS;

use App\Collections\Models\Saas\GPS\TrackingDataCollection;
use App\Entities\Saas\GPS\TrackingEntity;
use App\Http\Controllers\ApiController;
use App\Http\Requests\GPS\AlertIndexRequest;
use App\Http\Requests\GPS\TrackingRequest;
use App\Http\Resources\GPS\TrackingResource;
use App\Models\GPS\History;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TrackingController extends ApiController
{
    /**
     * Get tracking data list
     *
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\GET(
     *     path="/api/gps/tracking",
     *     tags={"Carrier GPS"},
     *     summary="Tracking data list",
     *     operationId="Tracking data list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *          @OA\Schema(type="string", default="asc", enum ={"asc","desc"})
     *     ),
     *     @OA\Parameter(name="search", in="query", description="Seach by driver_name or unit_number", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="device_statuses", in="query", required=false,
     *           description="device_statuses[]=inactive&device_statuses[]=deleted",
     *           @OA\Schema(type="string", enum ={"active", "inactive", "deleted"})
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/GPSTrackingResource")
     *     ),
     * )
     */
    public function index(TrackingRequest $request): AnonymousResourceCollection
    {
//        $this->authorize('gps alerts');

        $collection = new TrackingDataCollection();

        $trucks = Truck::query()
            ->has('gpsDeviceWithTrashed')
            ->filter($request->validated())
            ->with([
                'driver',
                'driver.trailer.driver',
                'driver.trailer.lastGPSHistory',
                'driver.trailer.gpsDeviceWithTrashed',
                'gpsDeviceWithTrashed',
                'lastGPSHistory',
                'lastGPSHistory.alerts'
            ])
            ->where('carrier_id', authUser()->getCompanyId())
            ->get()
        ;

        foreach ($trucks as $truck){
            if($truck->lastGPSHistory && $truck->lastGPSHistory->event_type == History::EVENT_CHANGE_DRIVER) continue;

            $collection->push(new TrackingEntity($truck));
        }

        // получаем id трейлеров, чтоб убрать их из выборки трейлеров
        $trailerIds = array_diff($collection->pluck('trailer.id')->toArray(), array(null));

        $trailers = Trailer::query()
            ->has('gpsDeviceWithTrashed')
            ->filter($request->validated())
            ->with([
                'driver',
                'gpsDeviceWithTrashed',
                'lastGPSHistory'
            ])
            ->where('carrier_id', authUser()->getCompanyId())
            ->whereNotIn('id', $trailerIds)
            ->get()
        ;

        foreach ($trailers as $trailer){
            if($trailer->lastGPSHistory && $trailer->lastGPSHistory->event_type == History::EVENT_CHANGE_DRIVER) continue;

            $collection->push(new TrackingEntity($trailer));
        }

        $sorted = $collection->sortByDriverName($request->order_type ?? 'asc');

        return TrackingResource::collection($sorted);
    }
}


