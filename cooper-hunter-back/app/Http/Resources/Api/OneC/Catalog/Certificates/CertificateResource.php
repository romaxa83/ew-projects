<?php

namespace App\Http\Resources\Api\OneC\Catalog\Certificates;

use App\Models\Catalog\Certificates\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Certificate
 */
class CertificateResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'link' => $this->link,
            'type' => $this->type->type,
        ];
    }
}
