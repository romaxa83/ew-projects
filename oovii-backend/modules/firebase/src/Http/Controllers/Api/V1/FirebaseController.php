<?php

namespace WezomCms\Firebase\Http\Controllers\Api\V1;

use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Throwable;
use WezomCms\Firebase\Http\Resources\V1\FcmNotificationResource;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Firebase\Repositories\FcmNotificationRepository;
use WezomCms\Users\Models\User;

class FirebaseController extends ApiController
{
    protected array $orderBySupport = ['id', 'created_at'];

    public function __construct(
        protected FcmNotificationRepository $repo,
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/notifications",
     *     tags={"Notifications"},
     *     security={{"Basic": {}}},
     *     summary="Get a notifications of user",
     *     @OA\Parameter(name="order_by", in="path", required=false,
     *         description="По какому полю сортировать, поддерживается - [id, created_at], если нужно передать несколько полей то так - ?order_by[]=id&order_by[]=created_at",
     *         @OA\Schema(type="string",example="id")
     *     ),
     *     @OA\Parameter(name="order_type", in="path", required=false,
     *         description="Тип сортировки, поддерживается - [asc, desc],по умолчанию используеться desc, если передается несколько полей для сортировки, то для каждого, последовательно, можно передовать тип - ?order_type[]=asc&order_type[]=desc",
     *         @OA\Schema(type="string",example="asc")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/FcmNotificationResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $models = $this->repo->getAllToFront(
                $user->id,
                $request->all(),
                $this->orderDataForQuery()
            );

            return self::successJsonMessage(FcmNotificationResource::collection($models));
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }
}
