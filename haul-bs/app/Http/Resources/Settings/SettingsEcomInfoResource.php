<?php

namespace App\Http\Resources\Settings;

use App\Http\Resources\Files\ImageResource;
use App\Models\Settings\Settings;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="SettingsEcomInfoResource", type="object",
 *     @OA\Property(property="data", type="object", description="Body Shop profile data", allOf={
 *         @OA\Schema(
 *             @OA\Property(property="ecommerce_logo", type="object", description="Logo for ecommerce", allOf={
 *                 @OA\Schema(ref="#/components/schemas/Image")
 *             }),
 *             @OA\Property(property="ecommerce_company_name", type="string", description="Name for ecommerce"),
 *             @OA\Property(property="ecommerce_address", type="string", description="Address for ecommerce"),
 *             @OA\Property(property="ecommerce_city", type="string", description="City for ecommerce"),
 *             @OA\Property(property="ecommerce_state_name", type="string", description="State name for ecommerce"),
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

class SettingsEcomInfoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            Settings::ECOMM_LOGO_FIELD => !empty($this['ecommerce_logo'])
                ? ImageResource::make($this['ecommerce_logo']->getFirstMedia(Settings::ECOMM_LOGO_FIELD))->getFullUrl() ?? null
                : null,
            'ecommerce_company_name' => $this['ecommerce_company_name']->value ?? null,
            'ecommerce_address' => $this['ecommerce_address']->value ?? null,
            'ecommerce_city' => $this['ecommerce_city']->value ?? null,
            'ecommerce_state_name' => !empty($this['ecommerce_state_name']->value)
                ? $this['ecommerce_state_name']->value
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
