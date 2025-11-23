<?php

namespace App\Http\Resources\Audits;

use App\Http\Resources\Clients\ClientResource;
use App\Http\Resources\Users\UserSimpleResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array
 */
class AuditLogResource extends JsonResource
{
    public function toArray($request)
    {
        $data = $this->resource;

        return [
            'audit_id' => $data['audit_id'] ?? null,
            'action' => $data['action'] ?? null,
            'entity' => $data['entity'] ?? null,
            'details' => $data['details'] ?? null,
            'user' => UserSimpleResource::make($data['user'] ?? null),
            'client' => ClientResource::make($data['client'] ?? null),
            'created_at' => $data['created_at'] ?? null,
            'is_client_activity' => $data['is_client_activity'] ?? null,
        ];
    }
}
