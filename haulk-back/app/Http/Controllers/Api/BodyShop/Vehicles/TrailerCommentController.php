<?php

namespace App\Http\Controllers\Api\BodyShop\Vehicles;

use App\Http\Controllers\Api\Vehicles\CommentController;
use App\Http\Requests\Vehicles\VehicleCommentRequest;
use App\Http\Resources\BodyShop\Vehicles\VehicleCommentResource;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Comments\TrailerComment;
use App\Models\Vehicles\Trailer;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Log;

class TrailerCommentController extends CommentController
{
    protected string $permissionsName = 'trailers';

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

    public function index(Request $request, Trailer $trailer)
    {
        return $this->indexComments($request, $trailer);
    }

    public function store(Trailer $trailer, VehicleCommentRequest $request)
    {
        return $this->storeComment($trailer, $request);
    }

    public function destroy(Request $request, Trailer $trailer, TrailerComment $comment): JsonResponse
    {
        return $this->destroyComment($request, $trailer, $comment);
    }

    /**
     * @OA\Get(
     *     path="/api/body-shop/trailers/{trailerId}/comments",
     *     tags={"Trailer Comments Body Shop"},
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
     *     path="/api/body-shop/trailers/{trailerId}/comments",
     *     tags={"Trailer comments Body Shop"},
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
     *     path="/api/body-shop/trailers/{trailerId}/comments/{commentId}",
     *     tags={"Trailer Comments  Body Shop"},
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
