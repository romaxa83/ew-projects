<?php

namespace App\Http\Resources\Saas\Companies;

use App\Http\Resources\Saas\GPS\GPSDeviceSubscriptionResource;
use App\Models\Saas\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class CompanyPaginatedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'active' => $this->active,
            'name' => $this->name,
            'ga_id' => $this->ga_id,
            'usdot' => $this->usdot,
            'mc_number' => $this->mc_number,
            'type' => 'carrier',
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_name' => $this->phone_name,
            'phone_extension' => $this->phone_extension,
            'fax' => $this->fax,
            'employees_count' => $this->getEmployeesCount(),
            'orders_count' => $this->getOrdersCount(),
            'created_at' => $this->created_at->timestamp,
            'registration_at' => $this->registration_at ? $this->registration_at->timestamp : null,
            'subscription_active' => $this->isSubscriptionActive(),
            'has_payment_method' => $this->hasPaymentMethod(),
            'payment_failed' => false,
            'has_unpaid_invoices' => $this->hasUnpaidInvoices(),
            'is_trial' => $this->isInTrialPeriod(),
            'use_in_body_shop' => $this->use_in_body_shop,
            'gps_devices_count' => $this->countActiveGpsDevices(),
            'gps_subscription' => GPSDeviceSubscriptionResource::make($this->gpsDeviceSubscription),
        ];
    }
}
