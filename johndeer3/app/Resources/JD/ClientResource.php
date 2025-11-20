<?php

namespace App\Resources\JD;

use App\Helpers\DateFormat;
use App\Models\JD\Client;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Client Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="jd_id", type="integer", description="ID в системе JD", example=4),
 *     @OA\Property(property="customer_id", type="string", description="ID customer", example="3611666"),
 *     @OA\Property(property="company_name", type="string", description="Название компании", example="СГ ТОВ 'МАКАРІВСЬКЕ'"),
 *     @OA\Property(property="customer_first_name", type="string", description="Имя представителя", example="Юрій"),
 *     @OA\Property(property="customer_last_name", type="string", description="Фамилия представителя", example="Салата"),
 *     @OA\Property(property="customer_second_name", type="string", description="Отчество представителя", example="Іванович"),
 *     @OA\Property(property="phone", type="string", description="Телефон", example="+380440000001"),
 *     @OA\Property(property="created", type="string", description="Создание", example="22.06.2020 10:48"),
 *     @OA\Property(property="updated", type="string", description="Обновление", example="22.06.2020 10:48"),
 *     @OA\Property(property="region", type="object", description="Region" , ref="#/components/schemas/RegionResource"),
 * )
 */

class ClientResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Client $client */
        $client = $this;

        return [
            'id' => $client->id,
            'jd_id' => $client->jd_id,
            'customer_id' => $client->customer_id,
            'company_name' => $client->company_name,
            'customer_first_name' => $client->customer_first_name,
            'customer_last_name' => $client->customer_last_name,
            'customer_second_name' => $client->customer_second_name,
            'phone' => $client->phone,
            'created' => DateFormat::front($client->created_at),
            'updated' => DateFormat::front($client->updated_at),
            'region' => $client->region ? RegionResource::make($client->region) : null,
        ];
    }
}
