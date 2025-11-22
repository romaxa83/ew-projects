<?php

namespace App\Http\Controllers\Api\V1\Vehicles\Trailer;

use App\Http\Controllers\Api\ApiController;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Requests\Vehicles\SameVinRequest;
use App\Http\Resources\Vehicles\SameVinResource;
use App\Models\Vehicles\Trailer;
use App\Repositories\Vehicles\TrailerRepository;
use App\Services\Vehicles\TrailerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class ActionController extends ApiController
{
    public function __construct(
        protected TrailerRepository $repo,
        protected TrailerService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/trailers/same-vin",
     *     tags={"Vehicles trailer"},
     *     security={{"Basic": {}}},
     *     summary="Get vehicles with the same vehicle vin",
     *     operationId="GetVehiclesWithTheSameVehicleVinTrailer",
     *     deprecated=false,
     *
     *     @OA\Parameter(name="id", in="query", description="Current Vehicle ID",required=false,
     *         @OA\Schema(type="integer",)
     *     ),
     *     @OA\Parameter(name="vin", in="query", description="", required=true,
     *         @OA\Schema(type="string",)
     *     ),
     *
     *     @OA\Response(response=200, description="Trailer paginated data",
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
            $this->repo->getTrailerWithVin($request->vin, $request->id)
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/trailers/{id}/attachments/{attachmentId}",
     *     tags={"Vehicles trailer"},
     *     security={{"Basic": {}}},
     *     summary="Delete attachment from trailer",
     *     operationId="DeleteAttachmentFromTrailer",
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
        $this->authorize(Permission\Trailer\TrailerUpdatePermission::KEY);

        /** @var $model Trailer */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.trailer.not_found")
        );

        $this->service->deleteFile($model, $attachmentId);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
