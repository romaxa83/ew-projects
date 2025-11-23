<?php

namespace App\Http\Resources\Calls;

use App\Http\Resources\Clients\ClientResource;
use App\Models\Calls\IncomingCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IncomingCall
 */
class IncomingCalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider->value,
            'phone' => $this->phone,
            'client' => ClientResource::make($this->client),
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
