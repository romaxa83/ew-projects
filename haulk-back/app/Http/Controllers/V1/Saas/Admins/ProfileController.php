<?php

namespace App\Http\Controllers\V1\Saas\Admins;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\Admins\ChangePasswordRequest;
use App\Http\Requests\Saas\Admins\ProfileUpdateRequest;
use App\Http\Requests\Saas\Admins\UploadPhotoRequest;
use App\Http\Resources\Saas\Admins\AdminProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfileController extends ApiController
{

    public function show(Request $request): AdminProfileResource
    {
        return AdminProfileResource::make(
            $request->user()
        );
    }

    public function update(ProfileUpdateRequest $request): AdminProfileResource
    {
        $admin = $request->user();
        $admin->update($request->validated());

        return AdminProfileResource::make($admin);
    }

    public function uploadPhoto(UploadPhotoRequest $request)
    {
        $request->user()->clearImageCollection();
        $request->user()->addImage(
            $request->file(
                $request->user()->getImageField()
            )
        );

        return AdminProfileResource::make($request->user());
    }

    public function deletePhoto(Request $request)
    {
        $request->user()->clearImageCollection();

        return AdminProfileResource::make($request->user())
            ->response()
            ->setStatusCode(204);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        if ($request->user()->updatePassword($request->input('password'))) {
            return $this->makeSuccessResponse();
        }

        return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

}

/**
 * @OA\Get(path="/v1/saas/admins/profile", tags={"Admin profile"}, summary="Get info about current admin", operationId="Get user data", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/AdminProfile")
 *     ),
 * )
 *
 * @OA\Put(path="/v1/saas/admins/profile", tags={"Admin profile"},summary="Update user info", operationId="Update user data", deprecated=false,
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
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/Profile")
 *     ),
 * )
 *
 * @OA\Post(path="/v1/saas/profile/upload-photo", tags={"Profile"}, summary="Upload photo", operationId="Upload photo", deprecated=false,
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
 *
 * @OA\Delete(
 *     path="/v1/saas/profile/delete-photo",
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
 *
 * @OA\Put(path="/v1/saas/admins/profile/change-password", tags={"Admin profile"}, summary="Change admin password", operationId="Change admin password", deprecated=false,
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
