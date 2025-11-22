<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Files\ImageResource;
use App\Models\Language;
use App\Models\Users\User;
use App\Services\Billing\BillingService;
use App\Services\Permissions\PermissionWorker;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     * @throws Exception
     *
     * @OA\Schema(schema="Profile", type="object",
     *            @OA\Property(property="data", type="object", description="Profile data", allOf={
     *                  @OA\Schema(required={"id", "full_name", "first_name", "last_name", "email","role_id"},
     *                          @OA\Property(property="id", type="integer", description="User id"),
     *                          @OA\Property(property="full_name", type="string", description="User full name"),
     *                          @OA\Property(property="first_name", type="string", description="User first name"),
     *                          @OA\Property(property="last_name", type="string", description="User last name"),
     *                          @OA\Property(property="company_name", type="string", description="Name user's company"),
     *                          @OA\Property(property="email", type="string", description="User email"),
     *                          @OA\Property(property="phone", type="string", description="User phone"),
     *                          @OA\Property(property="is_invoice_allowed", type="boolean", description="is_invoice_allowed"),
     *                          @OA\Property(property="phone_extension", type="string", description="User phone extension"),
     *                          @OA\Property(property="phones", type="array", description="User aditional phones",
     *                              @OA\Items(ref="#/components/schemas/PhonesRaw")),
     *                          @OA\Property(property="role_id", type="integer", description="Role id"),
     *                          @OA\Property(property="photo", type="object", description="image with different size", allOf={
     *                              @OA\Schema(ref="#/components/schemas/Image")
     *                          }),
     *                          @OA\Property(property="permissions",type="object",description="User permissions"),
     *                  )
     *              }
     *           ),
     * )
     *
     * @OA\Schema(schema="PhonesRaw", type="object", allOf={
     *                  @OA\Schema(
     *                          required={"number", "extension"},
     *                          @OA\Property(property="number", type="string", description="Phone number"),
     *                          @OA\Property(property="extension", type="string", description="Phone extension"),
     *                      )
     *           }
     * )
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this;
        $worker = new PermissionWorker();
        $permissions = $worker->getPermissionsProfile(
            $worker->getUserPermissions($user)
        );

        // add order-review permission to the list
        if ($user->can_check_orders) {
            $permissions['orders'][] = 'order-review';
        }

        /** @var $billingService BillingService */
        $billingService = resolve(BillingService::class);

        if ($billingService->ifHaveReadOnlyAccess($user->getCompany())) {
            $permissions = $billingService->readOnlyPermissionsFilter($permissions);
            $permissions = $billingService->gpsMenuPermissionsFilter($permissions, $this->resource);
        }

        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_invoice_allowed' => $user->carrier->notificationSettings->is_invoice_allowed ?? false,
            'phone_extension' => $user->phone_extension,
            'phones' => $user->phones,
            'role_id' => $user->roles->first()->id,
            $user->getImageField() => ImageResource::make($user->getFirstImage()),
            'language' => $user->language ?? Language::default()->first()->slug,
            'permissions' => $permissions,
        ];
    }
}
