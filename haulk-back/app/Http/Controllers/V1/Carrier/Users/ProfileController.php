<?php


namespace App\Http\Controllers\V1\Carrier\Users;


use App\Http\Resources\Saas\Companies\CompanyInfoResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class ProfileController extends \App\Http\Controllers\Api\Users\ProfileController
{
    /**
     * @throws AuthorizationException
     */
    public function companyInfo(Request $request): CompanyInfoResource
    {
        $this->authorize('profile read');

        $company = $request->user()->getCompany();

        return CompanyInfoResource::make($company);
    }
}

/**
 *
 * @OA\Get(path="/v1/carrier-mobile/profile/company-info",
 *     tags={"V1 Carrier-Mobile Profile"}, summary="Get company info", operationId="Get company info", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/CompanyInfoResource")
 *     ),
 * )
 *
 * @OA\Get(path="/v1/carrier-mobile/profile",
 *     tags={"V1 Carrier-Mobile Profile"}, summary="Get info about user", operationId="Get user data", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/Profile")
 *     ),
 * )
 *
 * @OA\Put(path="/v1/carrier-mobile/profile",
 *     tags={"V1 Carrier-Mobile Profile"}, summary="Update user info", operationId="Update user data", deprecated=false,
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
 *
 * @OA\Post(path="/v1/carrier-mobile/profile/upload-photo",
 *     tags={"V1 Carrier-Mobile Profile"}, summary="Upload photo", operationId="Upload photo", deprecated=false,
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
 *     path="/v1/carrier-mobile/profile/delete-photo",
 *     tags={"V1 Carrier-Mobile Profile"},
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
 * @OA\Put(path="/v1/carrier-mobile/profile/change-password",
 *     tags={"V1 Carrier-Mobile Profile"}, summary="Change user password", operationId="Change user password", deprecated=false,
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
 * @OA\Put(
 *     path="/v1/carrier-mobile/profile/set-fcm-token",
 *     tags={"V1 Carrier-Mobile Profile"},
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
