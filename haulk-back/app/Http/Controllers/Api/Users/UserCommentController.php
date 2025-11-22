<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Users\UserCommentRequest;
use App\Http\Resources\Users\UserCommentResource;
use App\Models\Users\User;
use App\Models\Users\UserComment;
use App\Services\Users\UserCommentService;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Log;
use Throwable;

class UserCommentController extends ApiController
{
    protected UserCommentService $service;

    public function __construct(UserCommentService $service)
    {
        parent::__construct();

        $this->service = $service->setUser(authUser());
    }

    /**
     * Display a listing of the resource.
     *
     * @param User $user
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/users/{userId}/comments",
     *     tags={"User comments"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UserCommentListResource")
     *     ),
     * )
     */
    public function index(User $user)
    {
        $this->authorize('users');

        if (!$user->isDriver() && !$user->isOwner()) {
            return $this->makeErrorResponse('', 404);
        }

        return UserCommentResource::collection( $this->service->getComments($user));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param User $user
     * @param UserCommentRequest $request
     * @return JsonResponse|UserCommentResource
     *
     * @OA\Post(
     *     path="/api/users/{userId}/comments",
     *     tags={"User comments"},
     *     summary="Create comment",
     *     operationId="Create comment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="comment",
     *          in="query",
     *          description="User comment",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserCommentResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(User $user, UserCommentRequest $request)
    {
        $this->authorize('users add-comment');

        if (!$user->isDriver() && !$user->isOwner()) {
            return $this->makeErrorResponse('', 404);
        }

        try {
            return new UserCommentResource($this->service->create(
                $user,
                $request->validated(),
                $request->header('TimezoneId', null)
            ));
        } catch (Exception $e) {

            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @param UserComment $comment
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/users/{userId}/comments/{commentId}",
     *     tags={"User comments"},
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
    public function destroy(User $user, UserComment $comment): JsonResponse
    {
        if ($this->user()->id !== $comment->author_id) {
            $this->authorize('users delete-comment');
        }

        try {
            $this->service->destroy($user, $comment);

            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }
}
