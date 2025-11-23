<?php

namespace WezomCms\Users\Http\Controllers\Api\V1;

use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Orders\Http\Resources\V1\OrderResource;
use WezomCms\Orders\Http\Resources\V1\OrdersCollection;
use WezomCms\Orders\Services\BonusService;
use WezomCms\SmsVerify\Services\SmsVerifyService;
use WezomCms\Users\Dto\UserDto;
use WezomCms\Users\Http\Requests\Api\V1\User;
use WezomCms\Users\Http\Resources\V1\UserBonusesResource;
use WezomCms\Users\Http\Resources\V1\UserResource;
use WezomCms\Users\Models\User as UserModel;
use WezomCms\Users\Services\Auth\UserPassportService;
use WezomCms\Users\Services\UserService;

class UserController extends ApiController
{
    public function __construct(
        protected BonusService $bonusService,
        protected UserService $service,
        protected SmsVerifyService $smsVerifyService,
        protected UserPassportService $passportService,
    ) {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/user",
     *     tags={"User"},
     *     security={{"Basic": {}}},
     *     @OA\Parameter(in="header", name="Content-Language", explode=true,
     *          @OA\Schema(default="kk", type="string",enum = {"ru", "en", "kk"}),
     *      ),
     *     summary="Get auth user",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function user(): JsonResponse
    {
        $user = Auth::user();

        try {
            return self::successJsonMessage(UserResource::make($user));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Put (
     *     path="/mobile/user",
     *     tags={"User"},
     *     security={{"Basic": {}}},
     *     summary="Edit user data",
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/EditRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param User\EditRequest $request
     * @return JsonResponse
     */
    public function edit(User\EditRequest $request): JsonResponse
    {
        $user = Auth::user();
        try {
            $model = $this->service->edit($user, UserDto::byEdit($request->all()));

            return self::successJsonMessage(UserResource::make($model));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Put (
     *     path="/mobile/user/change-phone",
     *     tags={"User"},
     *     security={{"Basic": {}}},
     *     summary="Change a user's phone",
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/ChangePhoneRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param User\ChangePhoneRequest $request
     * @return JsonResponse
     */
    public function changePhone(User\ChangePhoneRequest $request): JsonResponse
    {
        $user = Auth::user();
        try {
            $obj = $this->smsVerifyService->getAndCheckByActionToken($request['actionToken']);
            $obj->delete();

            $model = $this->service->changePhone($user, UserDto::byChangePhone($request->all()));

            return self::successJsonMessage(UserResource::make($model));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Delete  (
     *     path="/mobile/user",
     *     tags={"User"},
     *     security={{"Basic": {}}},
     *     summary="Delete a user profile",
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function delete(): JsonResponse
    {
        $user = Auth::user();
        try {
            if (!$user->canDeleteProfile()) {
                throw new Exception(__("cms-users::admin.message.can't delete profile"));
            }

            $this->passportService->logout($user);
            $this->service->delete($user);

            return self::successJsonMessage(__("cms-users::admin.message.delete profile"));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/user/bonus-history",
     *     tags={"User"},
     *     security={{"Basic": {}}},
     *     @OA\Parameter(in="header", name="Content-Language", explode=true,
     *          @OA\Schema(default="kk", type="string", enum = {"ru", "en", "kk"}),
     *     ),
     *     summary="Get auth user bonus history",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/UserBonusesResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function bonusHistory(): JsonResponse
    {
        $user = Auth::user();

        try {
            $user->loadMissing('inviterBonusHistory');

            return self::successJsonMessage(UserBonusesResource::make($user));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), errorCode($e));
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/user/orders",
     *     tags={"User"},
     *     security={{"Basic": {}}},
     *     summary="Get auth user orders",
     *     @OA\Parameter(in="header", name="Content-Language", explode=true,
     *          @OA\Schema(default="kk", type="string", enum = {"ru", "en", "kk"}),
     *     ),
     *
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Количество заказов на странице",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(name="page", in="query", required=false, description="Страница",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/OrdersCollection"
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
    public function orders(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = Auth::user();

        try {
            $limit = (int)$request->get('per_page', settings('orders.site.limit', 10));
            $orders = $user->orders()->paginate($limit);
            $orders->loadMissing(
                [
                    'useBonusHistory',
                ]
            );

            return self::successFromResponse(
                (new OrdersCollection($orders))->toResponse($request)
            );
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), errorCode($e));
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/user/orders/{order}",
     *     tags={"User"},
     *     security={{"Basic": {}}},
     *     @OA\Parameter(in="header", name="Content-Language", explode=true,
     *          @OA\Schema(default="kk", type="string", enum = {"ru", "en", "kk"}),
     *     ),
     *     summary="Get auth user single order",
     *
     *     @OA\Parameter(name="order", in="path", required=true, description="ID заказа",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/OrderResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param int $orderId
     * @return JsonResponse
     */
    public function order(int $orderId): JsonResponse
    {
        /** @var UserModel $user */
        $user = Auth::user();

        try {
            $order = $user->orders()
                ->where('id', $orderId)
                ->firstOrFail();

            return self::successJsonMessage(OrderResource::make($order));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), errorCode($e));
        }
    }
}

