<?php


namespace App\Http\Resources\Carrier;

use App\Http\Resources\Files\ImageResource;
use App\Http\Resources\Saas\GPS\GPSDeviceSubscriptionResource;
use App\Models\Saas\Company\Company;
use App\Repositories\Saas\GPS\DeviceRepository;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class CarrierResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(schema="CarrierProfileResource", type="object",
     *     @OA\Property(property="data", type="object", description="Carrier profile data", allOf={
     *         @OA\Schema(
     *             required={"name", "usdot", "address", "city", "state", "zip", "phones", "email"},
     *             @OA\Property(property="name", type="string", description="Carrier name"),
     *             @OA\Property(property="usdot", type="string", description="Carrier usdot"),
     *             @OA\Property(property="address", type="string", description="Carrier address"),
     *             @OA\Property(property="city", type="string", description="Carrier city"),
     *             @OA\Property(property="state_id", type="integer", description="Carrier state id"),
     *             @OA\Property(property="timezone", type="string", description="Carrier timezone"),
     *             @OA\Property(property="zip", type="string", description="Carrier zip"),
     *             @OA\Property(property="phone", type="string", description="Carrier phone"),
     *             @OA\Property(property="phone_name", type="string", description="Carrier contact name"),
     *             @OA\Property(property="phones", type="array", description="Carrier phones",
     *                 @OA\Items(type="object", allOf={
     *                     @OA\Schema(
     *                         @OA\Property(property="name", type="string", description="Contact person name"),
     *                         @OA\Property(property="number", type="string", description="Phone number"),
     *                     )
     *                 }),
     *             ),
     *             @OA\Property(property="email", type="string", description="Carrier email"),
     *             @OA\Property(property="fax", type="string", description="Carrier fax"),
     *             @OA\Property(property="website", type="string", description="Carrier site"),
     *             @OA\Property(property="billing_phone", type="string", description="Carrier billing phone"),
     *             @OA\Property(property="billing_phone_name", type="string", description="Carrier billing contact name"),
     *             @OA\Property(property="billing_phone_extension", type="string", description="Carrier billing phone extension"),
     *             @OA\Property(property="billing_phones", type="array", description="Carrier billing phones",
     *                 @OA\Items(type="object", allOf={
     *                     @OA\Schema(
     *                         @OA\Property(property="name", type="string", description="Contact person name"),
     *                         @OA\Property(property="number", type="string", description="Phone number"),
     *                         @OA\Property(property="extension", type="string", description="Phone extension"),
     *                     )
     *                 }),
     *             ),
     *             @OA\Property(property="billing_email", type="string", description="Carrier billing email"),
     *             @OA\Property(property="billing_payment_details", type="string", description="Carrier payment details"),
     *             @OA\Property(property="billing_terms", type="string", description="Carrier terms"),
     *             @OA\Property(property="logo", type="object", description="Carrier logo", allOf={
     *                 @OA\Schema(ref="#/components/schemas/Image")
     *             }),
     *             @OA\Property(property="gps_enabled", type="boolean", description="Is GPS for company enabled"),
     *             @OA\Property(property="speed_limit", type="float", description="Company speed limit"),
     *             @OA\Property(property="has_gps_devices", type="boolean", description="Does the company have devices?"),
     *             @OA\Property(property="has_active_at_vehicle", type="boolean", description="Check if the vehicle has active devices"),
     *             @OA\Property(property="gps_subscription", ref="#/components/schemas/GpsDeviceSubscriptionRawResource"),
     *             @OA\Property(property="driver_salary_contact_info", type="object", allOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="phones", type="array",
     *                         @OA\Items(ref="#/components/schemas/PhonesRaw")
     *                     ),
     *                 )
     *             })
     *         ),
     *     }),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'usdot' => (int) $this->usdot,
            'address' => $this->address,
            'city' => $this->city,
            'state_id' => $this->state_id,
            'zip' => $this->zip,
            'timezone' => $this->timezone,
            'phone' => $this->phone,
            'phone_name' => $this->phone_name,
            'phone_extension' => $this->phone_extension,
            'phones' => $this->phones,
            'email' => $this->email,
            'fax' => $this->fax,
            'website' => $this->website,
            'billing_phone' => $this->billingInfo ? $this->billingInfo->billing_phone : null,
            'billing_phone_name' => $this->billingInfo ? $this->billingInfo->billing_phone_name : null,
            'billing_phone_extension' => $this->billingInfo ? $this->billingInfo->billing_phone_extension : null,
            'billing_phones' => $this->billingInfo ? $this->billingInfo->billing_phones : null,
            'billing_email' => $this->billingInfo ? $this->billingInfo->billing_email : null,
            'billing_payment_details' => $this->billingInfo ? $this->billingInfo->billing_payment_details : null,
            'billing_terms' => $this->billingInfo ? $this->billingInfo->billing_terms : null,
            Company::LOGO_FIELD_CARRIER => ImageResource::make(
                $this->getFirstMedia(Company::LOGO_FIELD_CARRIER)
            ),
            'gps_enabled' => $this->isGPSEnabled(), // todo deprecate
            'speed_limit' => $this->speed_limit
                ? (float)$this->speed_limit
                : (float)config('gps.default_speed_limit'),
            'has_active_at_vehicle' => $this->activeAtVehicle(), // todo deprecate
            'has_gps_devices' => (boolean)$this->countGpsDevices(), // todo deprecate
            'gps_subscription' => GPSDeviceSubscriptionResource::make($this->gpsDeviceSubscription),
            'driver_salary_contact_info' => !empty($this->driver_salary_contact_info)
                ? [
                    'email' => $this->driver_salary_contact_info['email'] ?? null,
                    'phones' => $this->driver_salary_contact_info['phones'] ?? []
                ]
                : null,
        ];
    }

    // todo deprecate
    private function activeAtVehicle()
    {
        /** @var $repo DeviceRepository */
        $repo = resolve(DeviceRepository::class);

        return $repo->hasActiveAtVehicle($this->id);
    }
}
