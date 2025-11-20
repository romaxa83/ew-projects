<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Api\ApiController;
use App\Http\Request\User\ChangeLanguageRequest;
use App\Http\Request\User\ChangePasswordRequest;
use App\Http\Request\User\SetFcmTokenRequest;
use App\Models\Translate;
use App\Resources\User\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends ApiController
{
    public function __construct(
        protected UserService $service
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Post (
     *     path="/api/user/change-language",
     *     tags = {"User (for mobile)"},
     *     summary="Сменить язык приложения",
     *     security={{"Basic": {}}},
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/ChangeLanguageRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="User",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function changeLanguage(ChangeLanguageRequest $request): JsonResponse
    {
        $user = \Auth::user();
        try {
            Translate::assetLanguage($request->lang);

            $user = $this->service->changeLanguage($user, $request->lang);

            return $this->successJsonMessage(
                UserResource::make($user)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/user/change-password",
     *     tags = {"User (for mobile)"},
     *     summary="Сменить пароль",
     *     security={{"Basic": {}}},
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/ChangePasswordRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="User",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = \Auth::user();
        try {
            $user = $this->service->changePassword($user, $request->password);

            return $this->successJsonMessage(
                UserResource::make($user)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/user/set-fcm-token",
     *     tags = {"User (for mobile)"},
     *     summary="Записать fcm-token для пользователя",
     *     security={{"Basic": {}}},
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/SetFcmTokenRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="User",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function setFcmToken(SetFcmTokenRequest $request): JsonResponse
    {
        $user = \Auth::user();
        try {
            $user = $this->service->setFcmToken($user, $request['fcm_token']);

            return $this->successJsonMessage(UserResource::make($user));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
