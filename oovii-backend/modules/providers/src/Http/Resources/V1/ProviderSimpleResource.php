<?php

namespace WezomCms\Providers\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Providers\Models\Provider;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Provider simple Resource",
 *     description="Provider simple Resource",
 * )
 */
class ProviderSimpleResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $this Provider */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'phone' => $this->phone,
        ];
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID провайдера", example=1),
     *  @OA\Property(property="name", title="Name", description="Имя поставщика", example="Иван Иванов")
     *  @OA\Property(property="company", title="Company", description="Компания поставщика", example="Some company")
     *  @OA\Property(property="phony", title="Phony", description="Телефон поставщика", example="+380954514881")
     */
}
