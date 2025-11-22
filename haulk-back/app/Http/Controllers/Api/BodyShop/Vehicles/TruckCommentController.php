<?php

namespace App\Http\Controllers\Api\BodyShop\Vehicles;

use App\Http\Controllers\Api\Vehicles\CommentController;
use App\Http\Requests\Vehicles\VehicleCommentRequest;
use App\Http\Resources\BodyShop\Vehicles\VehicleCommentResource;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Comments\TruckComment;
use App\Models\Vehicles\Truck;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Log;

class TruckCommentController extends CommentController
{
    protected string $permissionsName = 'trucks';

    /**
     * @param Comment $comment
     *
     * @return JsonResponse|VehicleCommentResource
     */
     public function generateResource(Comment $comment)
     {
        return new VehicleCommentResource($comment);
     }

    /**
     * @param Collection $comments
     *
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function generateCollectionResource(Collection $comments)
    {
        return VehicleCommentResource::collection($comments);
    }

    public function index(Request $request, Truck $truck)
    {
        return $this->indexComments($request, $truck);
    }

    public function store(Truck $truck, VehicleCommentRequest $request)
    {
        return $this->storeComment($truck, $request);
    }

    public function destroy(Request $request, Truck $truck, TruckComment $comment): JsonResponse
    {
        return $this->destroyComment($request, $truck, $comment);
    }

    /**
     * @OA\Get(
     *     path="/api/body-shop/trucks/{truckId}/comments",
     *     tags={"Truck Comments Body Shop"},
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
     *         @OA\JsonContent(ref="#/components/schemas/VehicleCommentBSListResource")
     *     ),
     * )
     *
     *
     * @OA\Post(
     *     path="/api/body-shop/trucks/{truckId}/comments",
     *     tags={"Truck comments Body Shop"},
     *     summary="Create comment",
     *     operationId="Create comment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="comment", in="query", description="Vehicle comment", required=true,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleCommentBSResource")
     *     ),
     * )
     *
     *
     * @OA\Delete(
     *     path="/api/body-shop/trucks/{truckId}/comments/{commentId}",
     *     tags={"Trailer Comments Body Shop"},
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
     */
}
