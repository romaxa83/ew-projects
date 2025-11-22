<?php

namespace App\Http\Controllers\Api\BodyShop\Users;

use App\Http\Requests\V2\Users\ProfileRequest;
use App\Http\Requests\Users\UploadPhotoRequest;
use App\Http\Resources\BodyShop\Users\ProfileResource;

class ProfileController extends \App\Http\Controllers\Api\Users\ProfileController
{
    /**
     * @OA\Get(path="/api/body-shopprofile", tags={"Profile Body Shop"}, summary="Get info about user", operationId="Get user data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ProfileBS")
     *     ),
     * )
     *
     * @OA\Put(path="/api/body-shop/profile", tags={"Profile Body Shop"},summary="Update user info", operationId="Update user data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(name="first_name", in="query", description="User first name", required=true,
     *          @OA\Schema(type="string", default="Vlad",)
     *     ),
     *     @OA\Parameter(name="last_name", in="query", description="User last name", required=true,
     *          @OA\Schema(type="string", default="Chernenko",)
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
     *         @OA\JsonContent(ref="#/components/schemas/ProfileBS")
     *     ),
     * )
     *
     * @OA\Post(path="/api/body-shop/profile/upload-photo", tags={"Profile Body Shop"}, summary="Upload photo", operationId="Upload photo", deprecated=false,
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
     *         @OA\JsonContent(ref="#/components/schemas/ProfileBS")
     *     ),
     * )
     *
     * @OA\Delete(
     *     path="/api/body-shop/profile/delete-photo",
     *     tags={"Profile Body Shop"},
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
     *
     * @OA\Put(path="/api/body-shop/profile/change-password", tags={"Profile Body Shop"}, summary="Change user password", operationId="Change user password", deprecated=false,
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
     *
     * @todo moved
     */

    public function showBS(): ProfileResource
    {
        $this->authorize('profile read');

        return new ProfileResource($this->user);
    }

    /**
     * @param ProfileRequest $request
     * @return ProfileResource|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @todo moved
     */
    public function updateBS(ProfileRequest $request)
    {
        $this->authorize('profile update');

        $this->user->fill($request->validated());
        if ($this->user->save()) {
            return new ProfileResource($this->user);
        }
        return $this->makeErrorResponse(null, 500);
    }

    /**
     * @param UploadPhotoRequest $request
     * @return ProfileResource|\App\Http\Resources\Users\ProfileResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     *
     * @todo moved
     */
    public function uploadPhoto(UploadPhotoRequest $request)
    {
        $this->authorize('profile update');

        $this->user->clearImageCollection();
        $this->user->addImage($request->file($this->user->getImageField()));

        return ProfileResource::make($this->user);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @todo moved
     */
    public function deletePhoto()
    {
        $this->authorize('profile update');

        $this->user->clearImageCollection();
        return ProfileResource::make($this->user)
            ->response()
            ->setStatusCode(204);
    }
}
