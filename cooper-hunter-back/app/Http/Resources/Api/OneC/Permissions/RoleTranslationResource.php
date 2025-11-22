<?php

namespace App\Http\Resources\Api\OneC\Permissions;

use App\Models\Permissions\RoleTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RoleTranslation
 */
class RoleTranslationResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'language' => $this->language,
        ];
    }
}
