<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Users\ChangePasswordRequest;
use App\Http\Requests\Users\ProfileRequest;
use App\Http\Requests\Users\SetFcmTokenRequest;
use App\Http\Requests\Users\UploadPhotoRequest;
use App\Http\Resources\Users\ProfileResource;
use App\Models\BodyShop\Settings\Settings;
use App\Models\Users\User;
use App\Services\Events\EventService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;

class ProfileController extends ApiController
{
    /**
     * @var User
     */
    protected $user;

    /**
     * ProfileController constructor.
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware(
            function (Request $request, $next) {
                $this->user = $request->user();
                return $next($request);
            }
        );
    }

    /**
     * @return ProfileResource
     *
     * @OA\Get(path="/api/profile", tags={"Profile"}, summary="Get info about user", operationId="Get user data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Profile")
     *     ),
     * )
     * @throws AuthorizationException
     */
    public function show(): ProfileResource
    {
        $this->authorize('profile read');

        return new ProfileResource($this->user);
    }

    /**
     * @param ProfileRequest $request
     * @return ProfileResource|JsonResponse
     *
     * @OA\Put(path="/api/profile", tags={"Profile"},summary="Update user info", operationId="Update user data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(name="full_name", in="query", description="User full name", required=true,
     *          @OA\Schema(type="string", default="Vlad Chernenko",)
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="User phone", required=false,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phone_extension", in="query", description="Phone extension", required=false,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phones", in="query", description="Additional phone", required=false,
     *          @OA\Schema(type="array", description="User aditional phones",
     *              @OA\Items(ref="#/components/schemas/PhonesRaw")
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Profile")
     *     ),
     * )
     * @throws AuthorizationException
     */
    public function update(ProfileRequest $request)
    {
        $this->authorize('profile update');

        $event = EventService::users($this->user)
            ->setLoggedUser($this->user);

        $this->user->fill($request->validated());
        if ($this->user->save()) {
            $event->update();

            return new ProfileResource($this->user);
        }
        return $this->makeErrorResponse(null, 500);
    }

    /**
     * @param UploadPhotoRequest $request
     * @return ProfileResource
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig|AuthorizationException
     * @noinspection PhpUnused
     *
     * @OA\Post(path="/api/profile/upload-photo", tags={"Profile"}, summary="Upload photo", operationId="Upload photo", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\RequestBody(
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="photo", type="string", format="binary",)
     *              )
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Profile")
     *     ),
     * )
     */
    public function uploadPhoto(UploadPhotoRequest $request)
    {
        $this->authorize('profile update');

        $this->user->clearImageCollection();
        $this->user->addImage($request->file($this->user->getImageField()));

        return ProfileResource::make($this->user);
    }

    /**
     * @return JsonResponse
     * @noinspection PhpUnused
     *
     * @OA\Delete(
     *     path="/api/profile/delete-photo",
     *     tags={"Profile"},
     *     summary="Delete photo",
     *     operationId="Delete photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response(response=204, description="Successful operation",),
     * )
     * @throws AuthorizationException
     */
    public function deletePhoto()
    {
        $this->authorize('profile update');

        $this->user->clearImageCollection();
        return ProfileResource::make($this->user)
            ->response()
            ->setStatusCode(204);
    }

    /**
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     * @noinspection PhpUnused
     *
     * @OA\Put(path="/api/profile/change-password", tags={"Profile"}, summary="Change user password", operationId="Change user password", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(name="current_password", in="query", description="Current password",required=true,
     *          @OA\Schema(type="string", default="admin",)
     *     ),
     *     @OA\Parameter(name="password", in="query", description="New password", required=true,
     *          @OA\Schema(type="string", default="hello",)
     *     ),
     *     @OA\Parameter(name="password_confirmation", in="query", description="Confirm new password", required=true,
     *          @OA\Schema(type="string", default="hello",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",),
     * )
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $this->authorize('profile update');

        if ($this->user->updatePassword($request->input('password'))) {
            return $this->makeSuccessResponse();
        }
        return $this->makeErrorResponse(null, 500);
    }

    /**
     * @return UserResource|JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Put(
     *     path="/api/profile/set-fcm-token",
     *     tags={"Profile"},
     *     summary="Set user FCM token",
     *     operationId="Set user FCM token",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="token", in="path", description="User FCM token", required=true,
     *          @OA\Schema(type="string", default="",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     * )
     *
     */
    public function setFcmToken(SetFcmTokenRequest $request)
    {
        $this->authorize('profile update');

        try {
            $request->user()->fcm_token = $request->input('token');
            $request->user()->save();
            return $this->makeSuccessResponse(null, 200);
        } catch (Exception $e) {
            Log::error($e);
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }
}
