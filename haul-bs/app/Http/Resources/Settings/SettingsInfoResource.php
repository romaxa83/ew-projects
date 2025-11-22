<?php

namespace App\Http\Resources\Settings;

use App\Http\Resources\Files\ImageResource;
use App\Models\Settings\Settings;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="SettingsInfoResource", type="object",
 *     @OA\Property(property="data", type="object", description="Body Shop profile data", allOf={
 *         @OA\Schema(
 *             required={"company_name", "address", "city", "state", "zip", "phones", "email"},
 *             @OA\Property(property="company_name", type="string", description="Name"),
 *             @OA\Property(property="address", type="string", description="Address"),
 *             @OA\Property(property="city", type="string", description="City"),
 *             @OA\Property(property="state_id", type="integer", description="State id"),
 *             @OA\Property(property="timezone", type="string", description="Timezone"),
 *             @OA\Property(property="zip", type="string", description="Zip"),
 *             @OA\Property(property="phone", type="string", description="Phone"),
 *             @OA\Property(property="phone_name", type="string", description="Phone name"),
 *             @OA\Property(property="phone_extension", type="string", description="Phone extension"),
 *             @OA\Property(property="phones", type="array", description="Phones",
 *                 @OA\Items(type="object", allOf={
 *                     @OA\Schema(
 *                         @OA\Property(property="name", type="string", description="Contact person name"),
 *                         @OA\Property(property="number", type="string", description="Phone number"),
 *                         @OA\Property(property="extension", type="string", description="Phone extension"),
 *                     )}
 *                 ),
 *             ),
 *             @OA\Property(property="email", type="string", description="Email"),
 *             @OA\Property(property="fax", type="string", description="Fax"),
 *             @OA\Property(property="website", type="string", description="Site"),
 *             @OA\Property(property="billing_phone", type="string", description="Billing phone"),
 *             @OA\Property(property="billing_phone_name", type="string", description="Billing contact name"),
 *             @OA\Property(property="billing_phone_extension", type="string", description="Billing phone extension"),
 *             @OA\Property(property="billing_phones", type="array", description="Billing phones",
 *                 @OA\Items(type="object", allOf={
 *                     @OA\Schema(
 *                         @OA\Property(property="name", type="string", description="Contact person name"),
 *                         @OA\Property(property="number", type="string", description="Phone number"),
 *                         @OA\Property(property="extension", type="string", description="Phone extension"),
 *                     )}
 *                 ),
 *             ),
 *             @OA\Property(property="billing_email", type="string", description="Billing email"),
 *             @OA\Property(property="billing_payment_details", type="string", description="Payment details"),
 *             @OA\Property(property="billing_terms", type="string", description="Terms"),
 *             @OA\Property(property="logo", type="object", description="Logo", allOf={
 *                 @OA\Schema(ref="#/components/schemas/Image")
 *             }),
 *             @OA\Property(property="ecommerce_logo", type="object", description="Logo for ecommerce", allOf={
 *                 @OA\Schema(ref="#/components/schemas/Image")
 *             }),
 *             @OA\Property(property="ecommerce_company_name", type="string", description="Name for ecommerce"),
 *             @OA\Property(property="ecommerce_address", type="string", description="Address for ecommerce"),
 *             @OA\Property(property="ecommerce_city", type="string", description="City for ecommerce"),
 *             @OA\Property(property="ecommerce_state_id", type="integer", description="State id for ecommerce"),
 *             @OA\Property(property="ecommerce_zip", type="string", description="Zip for ecommerce"),
 *             @OA\Property(property="ecommerce_phone", type="string", description="Phone for ecommerce"),
 *             @OA\Property(property="ecommerce_phone_name", type="string", description="Phone name for ecommerce"),
 *             @OA\Property(property="ecommerce_phone_extension", type="string", description="Phone extension for ecommerce"),
 *             @OA\Property(property="ecommerce_phones", type="array", description="Phones for ecommerce",
 *                 @OA\Items(type="object", allOf={
 *                     @OA\Schema(
 *                         @OA\Property(property="name", type="string", description="Contact person name for ecommerce"),
 *                         @OA\Property(property="number", type="string", description="Phone number for ecommerce"),
 *                         @OA\Property(property="extension", type="string", description="Phone extension for ecommerce"),
 *                     )}
 *                 ),
 *             ),
 *             @OA\Property(property="ecommerce_email", type="string", description="Email for ecommerce"),
 *             @OA\Property(property="ecommerce_fax", type="string", description="Fax for ecommerce"),
 *             @OA\Property(property="ecommerce_website", type="string", description="Site for ecommerce"),
 *             @OA\Property(property="ecommerce_billing_phone", type="string", description="Billing phone for ecommerce"),
 *             @OA\Property(property="ecommerce_billing_phone_name", type="string", description="Billing contact name for ecommerce"),
 *             @OA\Property(property="ecommerce_billing_phone_extension", type="string", description="Billing phone extension for ecommerce"),
 *             @OA\Property(property="ecommerce_billing_phones", type="array", description="Billing phones for ecommerce",
 *                 @OA\Items(type="object", allOf={
 *                     @OA\Schema(
 *                         @OA\Property(property="name", type="string", description="Contact person name"),
 *                         @OA\Property(property="number", type="string", description="Phone number"),
 *                         @OA\Property(property="extension", type="string", description="Phone extension"),
 *                     )}
 *                 ),
 *              ),
 *              @OA\Property(property="ecommerce_billing_email", type="string", description="Billing email for ecommerce"),
 *              @OA\Property(property="ecommerce_billing_payment_details", type="string", description="Payment details for ecommerce"),
 *              @OA\Property(property="ecommerce_billing_terms", type="string", description="Terms for ecommerce"),
 *              @OA\Property(property="ecommerce_billing_payment_options", type="string", description="Payment options for ecommerce"),
 *         )}
 *     ),
 * )
 *
 */

class SettingsInfoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'company_name' => $this['company_name']->value ?? null,
            'address' => $this['address']->value ?? null,
            'city' => $this['city']->value ?? null,
            'state_id' => !empty($this['state_id']->value)
                ? (int) $this['state_id']->value
                : null,
            'zip' => $this['zip']->value ?? null,
            'timezone' => $this['timezone']->value ?? null,
            'phone' => $this['phone']->value ?? null,
            'phone_name' => $this['phone_name']->value ?? null,
            'phone_extension' => $this['phone_extension']->value ?? null,
            'phones' => !empty($this['phones']->value)
                ? json_decode($this['phones']->value)
                : [],
            'email' => $this['email']->value ?? null,
            'fax' => $this['fax']->value ?? null,
            'website' => $this['website']->value ?? null,
            'billing_phone' => $this['billing_phone']->value ?? null,
            'billing_phone_name' => $this['billing_phone_name']->value ?? null,
            'billing_phone_extension' => $this['billing_phone_extension']->value ?? null,
            'billing_phones' => !empty($this['billing_phones']->value)
                ? json_decode($this['billing_phones']->value)
                : [],
            'billing_email' => $this['billing_email']->value ?? null,
            'billing_payment_details' => $this['billing_payment_details']->value ?? null,
            'billing_terms' => $this['billing_terms']->value ?? null,
            Settings::LOGO_FIELD => !empty($this['logo'])
                ? ImageResource::make($this['logo']->getFirstMedia(Settings::LOGO_FIELD))
                : null,
            Settings::ECOMM_LOGO_FIELD => !empty($this['ecommerce_logo'])
                ? ImageResource::make($this['ecommerce_logo']->getFirstMedia(Settings::ECOMM_LOGO_FIELD))
                : null,
            'ecommerce_company_name' => $this['ecommerce_company_name']->value ?? null,
            'ecommerce_address' => $this['ecommerce_address']->value ?? null,
            'ecommerce_city' => $this['ecommerce_city']->value ?? null,
            'ecommerce_state_id' => !empty($this['ecommerce_state_id']->value)
                ? (int) $this['ecommerce_state_id']->value
                : null,
            'ecommerce_zip' => $this['ecommerce_zip']->value ?? null,
            'ecommerce_phone' => $this['ecommerce_phone']->value ?? null,
            'ecommerce_phone_name' => $this['ecommerce_phone_name']->value ?? null,
            'ecommerce_phone_extension' => $this['ecommerce_phone_extension']->value ?? null,
            'ecommerce_phones' => !empty($this['ecommerce_phones']->value)
                ? json_decode($this['ecommerce_phones']->value)
                : [],
            'ecommerce_email' => $this['ecommerce_email']->value ?? null,
            'ecommerce_fax' => $this['ecommerce_fax']->value ?? null,
            'ecommerce_website' => $this['ecommerce_website']->value ?? null,
            'ecommerce_billing_phone' => $this['ecommerce_billing_phone']->value ?? null,
            'ecommerce_billing_phone_name' => $this['ecommerce_billing_phone_name']->value ?? null,
            'ecommerce_billing_phone_extension' => $this['ecommerce_billing_phone_extension']->value ?? null,
            'ecommerce_billing_phones' => !empty($this['ecommerce_billing_phones']->value)
                ? json_decode($this['ecommerce_billing_phones']->value)
                : [],
            'ecommerce_billing_email' => $this['ecommerce_billing_email']->value ?? null,
            'ecommerce_billing_payment_details' => $this['ecommerce_billing_payment_details']->value ?? null,
            'ecommerce_billing_terms' => $this['ecommerce_billing_terms']->value ?? null,
            'ecommerce_billing_payment_options' => $this['ecommerce_billing_payment_options']->value ?? null,
        ];
    }
}
