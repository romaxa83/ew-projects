<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\ProfileRequest;
use App\Http\Requests\User\ProfileUploadRequest;
use App\Http\Resources\Users\ProfileResource;
use App\Models\Users\User;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Services\Users\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends ApiController
{

    public function __construct(
        protected UserService $service,
    )
    {}

    /**
     * @OA\Get (
     *     path="/api/v1/profile",
     *     tags={"Profile"},
     *     security={{"Basic": {}}},
     *     summary="Get profile",
     *     operationId="GetProfile",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Auth user profile",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResource")
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="403", description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function profile(): JsonResponse|ProfileResource
    {
        $this->authorize(Permission\Profile\ProfileReadPermission::KEY);

        return ProfileResource::make(Auth::user());
    }

    /**
     * @OA\Put (
     *     path="/api/v1/profile",
     *     tags={"Profile"},
     *     security={{"Basic": {}}},
     *     summary="Update user info",
     *     operationId="Update user info",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProfileRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Auth user profile",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResource")
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="403", description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(ProfileRequest $request): ProfileResource
    {
        $this->authorize(Permission\Profile\ProfileUpdatePermission::KEY);

        return ProfileResource::make(
            $this->service->updateProfile(auth_user(), $request->getDto())
        );
    }

    /**
     * @OA\Post (
     *     path="/api/v1/profile/upload-photo",
     *     tags={"Profile"},
     *     security={{"Basic": {}}},
     *     summary="Upload photo",
     *     operationId="ProfileUploadPhoto",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="photo", type="string", format="binary",)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Auth user profile",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResource")
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="403", description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function uploadPhoto(ProfileUploadRequest $request)
    {
        $this->authorize(Permission\Profile\ProfileUpdatePermission::KEY);

        /** @var $model User */
        $model = auth_user();

        return ProfileResource::make($this->service->uploadAvatar(
            $model, $request->file($model->getImageField())
        ));
    }


    /**
     * @OA\Delete (
     *     path="/api/v1/profile/delete-photo",
     *     tags={"Profile"},
     *     security={{"Basic": {}}},
     *     summary="Delete photo",
     *     operationId="ProfileDeletePhoto",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Response(response=204, description="Auth user profile",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResource")
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="403", description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function deletePhoto()
    {
        $this->authorize(Permission\Profile\ProfileUpdatePermission::KEY);

        /** @var $model User */
        $model = auth_user();

        return ProfileResource::make($this->service->deleteAvatar($model))
            ->response()
            ->setStatusCode(204);
    }
}
