<?php

namespace App\Http\Resources\Api\OneC\Projects;

use App\Http\Resources\Api\OneC\Technicians\TechnicianResource;
use App\Models\Projects\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'technician' => TechnicianResource::make($this->member),
            'name' => $this->name,
            'created_at' => $this->created_at->getTimestamp(),
        ];
    }
}


