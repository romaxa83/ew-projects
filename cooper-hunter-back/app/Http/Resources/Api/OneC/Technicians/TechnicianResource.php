<?php

namespace App\Http\Resources\Api\OneC\Technicians;

use App\Models\Technicians\Technician;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Technician
 */
class TechnicianResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => (string)$this->email,
            'phone' => (string)$this->phone,
            'guid' => $this->guid,
        ];
    }
}
