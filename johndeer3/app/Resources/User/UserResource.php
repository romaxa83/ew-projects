<?php

namespace App\Resources\User;

use App\Helpers\DateFormat;
use App\Models\User\User;
use App\Resources\Country\NationalityResource;
use App\Resources\JD\DealerResource;
use App\Resources\JD\EquipmentGroupByReportResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="User Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="login", type="string", description="Login", example="cubic"),
 *     @OA\Property(property="email", type="string", description="Email", example="cubic@rubic.com"),
 *     @OA\Property(property="phone", type="string", description="Телефон", example="+380500000001"),
 *     @OA\Property(property="status", type="boolean", description="Status", example=true),
 *     @OA\Property(property="profile", type="object", description="Profile",
 *         ref="#/components/schemas/UserProfileResource"
 *     ),
 *     @OA\Property(property="role", type="object", description="Role",
 *         ref="#/components/schemas/UserRoleResource"
 *     ),
 *     @OA\Property(property="lang", type="string", description="Язык приложения пользователя", example="ru"),
 *     @OA\Property(property="country", type="object", description="Nationality" , ref="#/components/schemas/NationalityResource"),
 *     @OA\Property(property="dealers", type="array", description="Dealers",
 *         @OA\Items(ref="#/components/schemas/DealerResource")
 *     ),
 *     @OA\Property(property="egs", type="array", description="Equipment groups, актуально для pss",
 *         @OA\Items(ref="#/components/schemas/EquipmentGroupByReportResource")
 *     ),
 *     @OA\Property(property="created", type="string", description="Создание пользователя", example="27.04.2022 22:23"),
 *     @OA\Property(property="updated", type="string", description="Редактирование пользователя", example="27.04.2022 22:23"),
 * )
 */

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var User $user */
        $user = $this;

        return [
            'id' => $user->id,
            'login' => $user->login,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'created' => DateFormat::front($user->created_at),
            'updated' => DateFormat::front($user->updated_at),
            'profile' => UserProfileResource::make($user->profile),
            'role' => UserRoleResource::make($user),
            'lang' => $user->lang,
            'country' => NationalityResource::make($user->country),
            'dealers' => $user->isTM() || $user->isTMD()
                ? DealerResource::collection($user->dealers)
                : [DealerResource::make($user->dealer)],
            'egs' => EquipmentGroupByReportResource::collection($user->egs)
        ];
    }
}
