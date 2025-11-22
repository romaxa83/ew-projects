<?php

namespace App\Http\Controllers\Api\Vehicles;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Vehicles\VehicleCommentRequest;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Vehicle;
use App\Services\Vehicles\VehicleCommentService;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Log;
use Throwable;

abstract class CommentController extends ApiController
{
    protected VehicleCommentService $service;

    protected string $permissionsName;

    /**
     * @param Comment $comment
     *
     * @return JsonResponse
     */
    abstract public function generateResource(Comment $comment);

    /**
     * @param Collection $comments
     *
     * @return JsonResponse|AnonymousResourceCollection
     */
    abstract public function generateCollectionResource(Collection $comments);

    public function __construct(VehicleCommentService $service)
    {
        parent::__construct();

        $this->service = $service->setUser(authUser());
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     */
    protected function indexComments(Request $request, Vehicle $vehicle)
    {
        $this->authorize($this->permissionsName);

        return $this->generateCollectionResource(
            $this->service->getComments($vehicle)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Vehicle $vehicle
     * @param VehicleCommentRequest $request
     * @return JsonResponse|VehicleCommentRequest
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    protected function storeComment(Vehicle $vehicle, VehicleCommentRequest $request)
    {
        $this->authorize($this->permissionsName . ' add-comment');

        try {
            return $this->generateResource($this->service->create(
                $vehicle,
                $request->validated(),
                $request->header('TimezoneId', null)
            ));
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Vehicle $vehicle
     * @param Comment $comment
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    protected function destroyComment(Request $request, Vehicle $vehicle, Comment $comment): JsonResponse
    {
        if ($this->user()->id !== $comment->user_id) {
            $this->authorize($this->permissionsName . ' delete-comment');
        }

        try {
            $this->service->destroy($vehicle, $comment);

            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }
}
