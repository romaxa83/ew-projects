<?php

namespace App\Http\Resources\Api\OneC\Projects;

use App\Models\Projects\System;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin System
 */
class SystemResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'warranty_status' => $this->warranty_status,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at->getTimestamp(),
            'project' => ProjectResource::make($this->project)
        ];
    }
}

