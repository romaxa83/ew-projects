<?php

namespace App\Http\Resources\Clients;

use App\Models\Client;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Client
 */
class ClientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->name,
            'last_name' => $this->lname,
        ];
    }
}
