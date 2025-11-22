<?php

namespace App\Http\Resources\Api\OneC\Dealer;

use App\Models\Companies\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class CompanyResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'guid' => $this->guid,
            'status' => $this->status,
            'type' => $this->type,
            'business_name' => $this->business_name,
            'email' => $this->email?->getValue(),
            'phone' => $this->phone?->getValue(),
            'fax' => $this->fax?->getValue(),
            'country' => $this->country->country_code,
            'state' => $this->state->short_name,
            'city' => $this->city,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'po_box' => $this->po_box,
            'zip' => $this->zip,
            'taxpayer_id' => $this->taxpayer_id,
            'tax' => $this->tax,
            'websites' => $this->websites,
            'marketplaces' => $this->marketplaces,
            'trade_names' => $this->trade_names,
        ];
    }
}

