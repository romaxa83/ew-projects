<?php

namespace App\Http\Resources\Saas\Companies;

use App\Http\Resources\Locations\StateResource;
use App\Http\Resources\Saas\GPS\GPSDeviceSubscriptionResource;
use App\Models\Saas\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class CompanyResource extends JsonResource
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
            'usdot' => $this->usdot,
            'ga_id' => $this->ga_id,
            'mc_number' => $this->mc_number,
            'type' => 'carrier',
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => StateResource::make($this->state),
            'state_id' => $this->state_id,
            'zip' => $this->zip,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_name' => $this->phone_name,
            'phone_extension' => $this->phone_extension,
            'phones' => $this->phones,
            'fax' => $this->fax,
            'website' => $this->website,
            'timezone' => $this->timezone,
            'status' => $this->getCompanyStatus(),
            'employees_count' => $this->getEmployeesCount(),
            'orders_count' => $this->getOrdersCount(),
            'created_at' => $this->created_at->timestamp,
            'registration_at' => $this->registration_at ? $this->registration_at->timestamp : null,
            'subscription_active' => $this->isSubscriptionActive(),
            'has_payment_method' => $this->hasPaymentMethod(),
            'payment_failed' => $this->lastPaymentAttemptFailed(),
            'has_unpaid_invoices' => $this->hasUnpaidInvoices(),
            'is_trial' => $this->isInTrialPeriod(),
            'is_exclusive' => $this->isExclusivePlan(),
            'use_in_body_shop' => $this->use_in_body_shop,
            'has_gps_devices' => (boolean)$this->countGpsDevices(), // todo delete
            'gps_subscription' => GPSDeviceSubscriptionResource::make($this->gpsDeviceSubscription),
        ];
    }
}

/**
 *
 * @OA\Schema(schema="CompanyResourceRaw", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="active", type="boolean"),
 *             @OA\Property(property="usdot", type="integer"),
 *             @OA\Property(property="ga_id", type="string"),
 *             @OA\Property(property="mc_number", type="integer"),
 *             @OA\Property(property="type", type="string"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="address", type="string"),
 *             @OA\Property(property="city", type="string"),
 *             @OA\Property(property="state", ref="#/components/schemas/StateRaw",),
 *             @OA\Property(property="zip", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="phone", type="string"),
 *             @OA\Property(property="phone_name", type="string"),
 *             @OA\Property(property="phones", type="object"),
 *             @OA\Property(property="fax", type="string"),
 *             @OA\Property(property="website", type="string"),
 *             @OA\Property(property="timezone", type="string"),
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(property="employees_count", type="integer"),
 *             @OA\Property(property="orders_count", type="integer"),
 *             @OA\Property(property="is_exclusive", type="boolean"),
 *             @OA\Property(property="created_at", type="integer"),
 *             @OA\Property(property="registration_at", type="integer"),
 *             @OA\Property(property="use_in_body_shop", type="boolean"),
 *             @OA\Property(property="has_gps_devices", type="boolean", description="Does the company have devices?"),
 *             @OA\Property(property="gps_enabled", type="boolean"),
 *             @OA\Property(property="gps_subscription", ref="#/components/schemas/GpsDeviceSubscriptionRawResource")
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="CompanyPaginatedResourceRaw", type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="active", type="boolean"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="usdot", type="integer"),
 *             @OA\Property(property="ga_id", type="string"),
 *             @OA\Property(property="mc_number", type="integer"),
 *             @OA\Property(property="type", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="phone", type="string"),
 *             @OA\Property(property="fax", type="string"),
 *             @OA\Property(property="employees_count", type="integer"),
 *             @OA\Property(property="orders_count", type="integer"),
 *             @OA\Property(property="created_at", type="integer"),
 *             @OA\Property(property="registration_at", type="integer"),
 *             @OA\Property(property="use_in_body_shop", type="boolean"),
 *             @OA\Property(property="gps_devices_count", type="integer")
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="CompanyResource", type="object",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/CompanyResourceRaw")
 *         }
 *     )
 * )
 *
 * @OA\Schema(schema="CompanyPaginatedResource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/CompanyPaginatedResourceRaw")
 *     ),
 *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * )
 *
 */
