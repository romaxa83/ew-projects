<?php

namespace WezomCms\Orders\Http\Controllers\Api\V1;

use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Orders\Dto\UserAddressDto;
use WezomCms\Orders\Http\Requests\Api\V1\AddressRequest;
use WezomCms\Orders\Http\Resources\V1\AddressResource;
use WezomCms\Orders\Models\UserAddress;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\Models\User;
use WezomCms\Users\Services\UserService;

class AddressController extends ApiController
{
    public function __construct(
        private UserService $userService
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/addresses",
     *     security={{"Basic": {}}},
     *     tags={"User address"},
     *     summary="Get user addresses list",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/AddressResource")
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
    public function index(): JsonResponse|AnonymousResourceCollection
    {
        Telegram::info("ROUTE - /mobile/addresses");
        $user = Auth::user();

        try {
            return AddressResource::collection($user->addresses);
        } catch(Exception $e){
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), errorCode($e));
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/addresses",
     *     security={{"Basic": {}}},
     *     tags={"User address"},
     *     summary="Add user address",
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AddressRequest")),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/AddressResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param AddressRequest $request
     * @return JsonResponse
     */
    public function store(AddressRequest $request): JsonResponse
    {
        Telegram::info("ROUTE - POST /mobile/addresses");
        try {
            /** @var User $user */
            $user = Auth::user();

            $address = $this->userService->createUserAddress(
                $user,
                UserAddressDto::fromArray($request->all())
            );

            return self::successJsonMessage(AddressResource::make($address), Response::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), errorCode($e));
        }
    }

    /**
     * @OA\Put (
     *     path="/mobile/addresses/{id}",
     *     security={{"Basic": {}}},
     *     tags={"User address"},
     *     summary="Update user address",
     *
     *     @OA\Parameter(name="id", in="path", required=true,
     *         description="Id адреса",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AddressRequest")),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/AddressResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param UserAddress $address
     * @param AddressRequest $request
     * @return JsonResponse
     */
    public function update(UserAddress $address, AddressRequest $request): JsonResponse
    {
        Telegram::info("ROUTE - PUT /mobile/addresses");
        try {
            $this->authorize('update', $address);

            $address = $this->userService->updateUserAddress(
                $address,
                UserAddressDto::fromArray($request->all())
            );

            return self::successJsonMessage(AddressResource::make($address));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), errorCode($e));
        }
    }

    /**
     * @OA\Delete (
     *     path="/mobile/addresses/{id}",
     *     security={{"Basic": {}}},
     *     tags={"User address"},
     *     summary="Delete user address",
     *
     *     @OA\Parameter(name="id", in="path", required=true,
     *         description="Id адреса",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param UserAddress $address
     * @return JsonResponse
     */
    public function destroy(UserAddress $address): JsonResponse
    {
        Telegram::info("ROUTE - DELETE /mobile/addresses");
        try {
            $this->authorize('delete', $address);

            $address->delete();

            return self::successJsonMessage(__('cms-orders::site.User address deleted'));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), errorCode($e));
        }
    }
}
