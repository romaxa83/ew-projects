<?php

namespace App\Http\Controllers\Api\V1\Vehicles\Truck;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Vehicles\SameVinRequest;
use App\Http\Resources\Vehicles\SameVinResource;
use App\Models\Vehicles\Truck;
use App\Repositories\Vehicles\TruckRepository;
use App\Services\Vehicles\TruckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class ActionController extends ApiController
{
    public function __construct(
        protected TruckRepository $repo,
        protected TruckService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/trucks/same-vin",
     *     tags={"Vehicles truck"},
     *     security={{"Basic": {}}},
     *     summary="Get vehicles with the same vehicle vin",
     *     operationId="GetVehiclesWithTheSameVehicleVin",
     *     deprecated=false,
     *
     *     @OA\Parameter(name="id", in="query", description="Current Vehicle ID",required=false,
     *         @OA\Schema(type="integer",)
     *     ),
     *     @OA\Parameter(name="vin", in="query", description="", required=true,
     *         @OA\Schema(type="string",)
     *     ),
     *
     *     @OA\Response(response=200, description="Truck paginated data",
     *          @OA\JsonContent(ref="#/components/schemas/SameVinResource")
     *      ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function sameVin(SameVinRequest $request): ResourceCollection
    {
        return SameVinResource::collection(
            $this->repo->getTrucksWithVin($request->vin, $request->id)
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/trucks/{id}/attachments/{attachmentId}",
     *     tags={"Vehicles truck"},
     *     security={{"Basic": {}}},
     *     summary="Delete attachment from truck",
     *     operationId="DeleteAttachmentFromTruck",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(name="{attachmentId}", in="path", required=true, description="ID attachment entity",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function deleteAttachment($id, $attachmentId): JsonResponse
    {
        $this->authorize(Permission\Truck\TruckUpdatePermission::KEY);

        /** @var $model Truck */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.truck.not_found")
        );

        $this->service->deleteFile($model, $attachmentId);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
