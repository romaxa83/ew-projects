<?php

namespace App\Http\Resources\Api\OneC\Dealer;

use App\Models\Dealers\Dealer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Dealer
 */
class DealerResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'guid' => $this->guid,
            'email' => $this->email->getValue(),
            'phone' => $this->phone?->getValue(),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => CompanyResource::make($this->company)
        ];
    }
}
