<?php

namespace App\Http\Controllers\Api\V1\Webhooks;

use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Webhooks\Vehicle\SetRequest;
use App\Http\Requests\Webhooks\Vehicle\UpdateOrCreateRequest;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Repositories\Vehicles\TrailerRepository;
use App\Repositories\Vehicles\TruckRepository;
use App\Services\Vehicles\TrailerService;
use App\Services\Vehicles\TruckService;
use App\Services\Vehicles\VehicleSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class VehicleController extends ApiController
{
    public function __construct(
        protected VehicleSyncService $service,
        protected TrailerService $trailerService,
        protected TrailerRepository $trailerRepo,
        protected TruckService $truckService,
        protected TruckRepository $truckRepo,
    )
    {}
    public function setVehicles(SetRequest $request): JsonResponse
    {
        $this->service->setVehicles($request->validated('data'));

//        logger_info('WEBHOOK VEHICLE SET', $request->all());

        if(empty($request->validated('data'))){
            return $this->successJsonMessage('Not set data, empty');
        }

        return $this->successJsonMessage('Set data');
    }

    public function unsetVehicles($companyId): JsonResponse
    {
        Truck::query()
            ->where('company_id', $companyId)
            ->each(fn (Truck $i) => $i->delete());
        Trailer::query()
            ->where('company_id', $companyId)
            ->each(fn (Trailer $i) => $i->delete());

//        logger_info('WEBHOOK VEHICLE UNSET', ['company_id' => $companyId]);

        return $this->successJsonMessage('Unset data');
    }

    public function createOrUpdate(UpdateOrCreateRequest $request): JsonResponse
    {
        logger_info('WEBHOOK VEHICLE SYNC', $request->validated());

        $this->service->syncVehicle($request->validated('data'));

        return $this->successJsonMessage('Sync vehicle');
    }

    public function deleteTrailer($id): JsonResponse
    {
        logger_info('[webhook] TRAILER DELETE', ['origin_id' => $id]);

        try {
            /** @var $model Trailer */
            $model = $this->trailerRepo->getByOriginId($id);

            $this->trailerService->delete($model);

        } catch (HasRelatedEntitiesException) {
            return $this->errorJsonMessage('The model is not deleted, there are related entities', Response::HTTP_BAD_REQUEST);
        }
        catch (\Throwable $e){
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
        return $this->successJsonMessage('Delete trailer');
    }

    public function deleteTruck($id): JsonResponse
    {
        logger_info('[webhook] TRUCK DELETE', ['origin_id' => $id]);

        try {
            /** @var $model Truck */
            $model = $this->truckRepo->getByOriginId($id);

            $this->truckService->delete($model);

        } catch (HasRelatedEntitiesException) {
            return $this->errorJsonMessage('The model is not deleted, there are related entities', Response::HTTP_BAD_REQUEST);
        }
        catch (\Throwable $e){
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
        return $this->successJsonMessage('Delete truck');
    }
}
