<?php

namespace App\Resources\Report;

use App\Models\Report\ReportClient as Client;
use App\Resources\JD\ModelDescriptionResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="ReportClient",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="customer_id", type="string", description="ID клиента", example="3851254"),
 *     @OA\Property(property="customer_first_name", type="string", description="Имя представителя", example="Юрій"),
 *     @OA\Property(property="customer_last_name", type="string", description="Фамилия представителя", example="Салата"),
 *     @OA\Property(property="company_name", type="string", description="Названия компании", example="СГ ТОВ 'МАКАРІВСЬКЕ'"),
 *     @OA\Property(property="phone", type="string", description="Телефон", example="+380449132251"),
 *     @OA\Property(property="status", type="boolean", description="Активен", example=true),
 *     @OA\Property(property="type", type="integer", description="Тип клиента (0/1 - конкурент/потенциальный)", example=1),
 *     @OA\Property(property="quantity_machine", type="integer", description="Кол-во техники у клиента", example=7),
 *     @OA\Property(property="comment", type="string", description="Комментария по клиенту", example="хороший клиент"),
 *     @OA\Property(property="model_description", type="object",
 *         ref="#/components/schemas/ModelDescriptionResource"
 *     )
 * )
 */

class ReportClient extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Client $client */
        $client = $this;

        return [
            'id' => $client->id,
            'customer_id' => $client->customer_id,
            'customer_first_name' => $client->customer_first_name,
            'customer_last_name' => $client->customer_last_name,
            'company_name' => $client->company_name,
            'phone' => $client->phone,
            'status' => $client->status,
            'type' => $client->pivot->type,
            'model_description' => ModelDescriptionResource::make($client->modelDescription()),
            'quantity_machine' => $client->pivot->quantity_machine,
            'comment' => $client->comment,
        ];
    }
}
