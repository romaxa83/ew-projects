<?php

namespace App\Http\Controllers\V1\Saas\Notifications;

use App\Enums\Notifications\NotificationStatus;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Notifications\NotificationFilterRequest;
use App\Http\Requests\Notifications\NotificationReadRequest;
use App\Http\Resources\Notifications\NotificationResource;
use App\Models\Notifications\Notification;
use App\Repositories\Notifications\NotificationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends ApiController
{
    protected NotificationRepository $repo;

    public function __construct(
        NotificationRepository $repo
    )
    {
        parent::__construct();

        $this->repo = $repo;
    }

    /**
     * @OA\GET(
     *     path="/v1/saas/notifications",
     *     tags={"Notifications"},
     *     summary="Get notifications list",
     *     operationId="GetNotificationList",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="status", in="query", required=false,
     *           @OA\Schema(type="string", example="new", enum={"new", "read"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/NotificationPaginatedResource")
     *     ),
     * )
     */
    public function index(NotificationFilterRequest $request): AnonymousResourceCollection
    {
        $models = $this->repo->getAllPagination(
            $request->validated()
        );

        return NotificationResource::collection($models);
    }

    /**
     * @OA\PUT(
     *     path="/v1/saas/notifications/read",
     *     tags={"Notifications"},
     *     summary="Update notification",
     *     operationId="UpdatedNotification",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="id", in="query", description="Array notifications id", required=true,
     *          @OA\Schema(type="array", @OA\Items())
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SimpleResponse")
     *     ),
     * )
     */
    public function read(NotificationReadRequest $request): JsonResponse
    {
        try {
            return $this->makeResponse(Notification::query()
                ->whereIn('id', $request['id'])
                ->update(['status' => NotificationStatus::READ]));

        } catch (\Throwable $e) {
            return $this->makeResponse(false);
        }
    }

    /**
     * @OA\GET(
     *     path="/v1/saas/notifications/count",
     *     tags={"Notifications"},
     *     summary="Count notification",
     *     operationId="CountNotification",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="status", in="query", required=false,
     *           @OA\Schema(type="string", example="new", enum={"new", "read"})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SimpleResponse")
     *     ),
     * )
     */
    public function count(NotificationFilterRequest $request): JsonResponse
    {
        try {
            return $this->makeResponse(Notification::query()
                ->filter($request->validated())
                ->count());

        } catch (\Throwable $e) {
            return $this->makeResponse(false);
        }
    }
}
