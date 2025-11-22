<?php

namespace App\Http\Controllers\Api\BodyShop\VehicleOwners;

use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\VehicleOwners\VehicleOwnerCommentRequest;
use App\Http\Resources\BodyShop\VehicleOwners\VehicleOwnerCommentResource;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\BodyShop\VehicleOwners\VehicleOwnerComment;
use App\Services\BodyShop\VehicleOwners\VehicleOwnerCommentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Log;

class VehicleOwnerCommentController extends ApiController
{
    protected VehicleOwnerCommentService $service;

    public function __construct(VehicleOwnerCommentService $service)
    {
        parent::__construct();

        $this->service = $service->setUser(authUser());
    }

    /**
     * Display a listing of the resource.
     *
     * @param VehicleOwner $vehicleOwner
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/body-shop/vehicle-owners/{vehicleOwnerId}/comments",
     *     tags={"Vehicle Owner comments Body Shop"},
     *     summary="Get comments paginated list",
     *     operationId="Get comments data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleOwnerCommentListResource")
     *     ),
     * )
     */
    public function index(VehicleOwner $vehicleOwner)
    {
        $this->authorize('vehicle-owners');

        return VehicleOwnerCommentResource::collection($this->service->getComments($vehicleOwner));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VehicleOwner $vehicleOwner
     * @param VehicleOwnerCommentRequest $request
     * @return JsonResponse|VehicleOwnerCommentResource
     *
     * @OA\Post(
     *     path="/api/body-shop/vehicle-owners/{vehicleOwnerId}/comments",
     *     tags={"Vehicle Owner comments Body Shop"},
     *     summary="Create comment",
     *     operationId="Create comment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="comment", in="query", description="Order comment", required=true,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleOwnerCommentResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(VehicleOwner $vehicleOwner, VehicleOwnerCommentRequest $request)
    {
        $this->authorize('vehicle-owners add-comment');

        try {
            return new VehicleOwnerCommentResource($this->service->create($vehicleOwner, $request->validated()));
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param VehicleOwner $vehicleOwner
     * @param VehicleOwnerComment $comment
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/body-shop/vehicle-owners/{vehicleOwnerId}/comments/{commentId}",
     *     tags={"Vehicle Owner comments Body Shop"},
     *     summary="Delete comment",
     *     operationId="Delete comment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     *
     * @throws Exception
     */
    public function destroy(VehicleOwner $vehicleOwner, VehicleOwnerComment $comment): JsonResponse
    {
        $this->authorize('vehicle-owners delete-comment');

        try {
            $this->service->destroy($vehicleOwner, $comment);

            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }
}
