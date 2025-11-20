<?php

namespace App\Resources\JD;

use App\Helpers\DateFormat;
use App\Models\JD\Dealer;
use App\Resources\Country\NationalityResource;
use App\Resources\User\UserSimpleResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Dealer Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="jd_id", type="integer", description="ID в системе JD", example=4),
 *     @OA\Property(property="jd_jd_id", type="string", description="поле jd_id в системе JD", example="482GP1"),
 *     @OA\Property(property="name", type="string", description="Название", example="Agristar"),
 *     @OA\Property(property="status", type="boolean", description="Статус", example=true),
 *     @OA\Property(property="country", type="object", description="Nationality" , ref="#/components/schemas/NationalityResource"),
 *     @OA\Property(property="created", type="string", description="Создание", example="22.06.2020 10:48"),
 *     @OA\Property(property="updated", type="string", description="Обновление", example="22.06.2020 10:48"),
 *     @OA\Property(property="users", type="array", description="Simple user",
 *         @OA\Items(ref="#/components/schemas/UserSimpleResource")
 *     ),
 * )
 */

class DealerResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Dealer $dealer */
        $dealer = $this;

        return [
            'id' => $dealer->id,
            'jd_id' => $dealer->jd_id,
            'jd_jd_id' => $dealer->jd_jd_id,
            'name' => $dealer->name,
            'status' => $dealer->status,
            'country' => NationalityResource::make($dealer->nationality),
            'created' => DateFormat::front($dealer->created_at),
            'updated' => DateFormat::front($dealer->updated_at),
            'users' => UserSimpleResource::collection($dealer->users)
        ];
    }

    /**
     * @SWG\Definition(definition="DealerResource",
     *     @SWG\Property(property="id", type="integer", example = "ID"),
     *     @SWG\Property(property="jd_id", type="integer", example = "ID в системе JD"),
     *     @SWG\Property(property="jd_jd_id", type="string", example = "поле jd_id в системе JD"),
     *     @SWG\Property(property="name", type="string", example = "Название"),
     *     @SWG\Property(property="status", type="boolean", example = "статус"),
     *     @SWG\Property(property="country", type="object", example="Nationality", ref="#/definitions/NationalityResource"),
     *     @SWG\Property(property="created", type="string", example = "Создание"),
     *     @SWG\Property(property="updated", type="string", example = "Обновление"),
     *     @SWG\Property(property="users", type="object", example="User", ref="#/definitions/UserSimpleResource"),
     * )
     */
}
