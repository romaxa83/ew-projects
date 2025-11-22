<?php

namespace App\Http\Resources\Api\OneC\Users;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
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
