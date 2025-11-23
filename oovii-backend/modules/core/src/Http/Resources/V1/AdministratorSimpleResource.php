<?php

namespace WezomCms\Core\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Core\Models\Administrator;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Administrator Resource (simple)",
 *     description="Administrator Resource (simple)",
 * )
 */
class AdministratorSimpleResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $this Administrator */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID товара", example=1),
     *  @OA\Property(property="name", title="Name", description="Имя", example="Иван Иванов")
     *  @OA\Property(property="email", title="Email", example="admin@gmail.com")
     */
}

