<?php

namespace App\Http\Requests\Settings;

use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Modules\Location\Models\State;
use App\Foundations\Modules\Location\Services\TimezoneService;
use App\Foundations\Rules\PhoneRule;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="SettingsInfoRequest",
 *     required={"company_name", "address", "city", "state_id", "zip", "phone", "email", "billing_phone"},
 *     @OA\Property(property="company_name", type="string", example="ADESA LAS VEGAS"),
 *     @OA\Property(property="address", type="string", example="1395 E 4th St, Reno, NV 89512"),
 *     @OA\Property(property="city", type="string", example="Reno"),
 *     @OA\Property(property="state_id", type="integer", example=1),
 *     @OA\Property(property="zip", type="string", example="89512"),
 *     @OA\Property(property="timezone", type="string", example="America/Los_Angeles"),
 *     @OA\Property(property="phone_name", type="string", example="John Doe"),
 *     @OA\Property(property="phone", type="string", example="1555555555"),
 *     @OA\Property(property="phone_extension", type="string", example="234"),
 *     @OA\Property(property="phones", type="array", description="aditional phones",
 *         @OA\Items(ref="#/components/schemas/PhonesSettingsRaw")
 *     ),
 *     @OA\Property(property="email", type="string", example="jack@mail.com"),
 *     @OA\Property(property="fax", type="string", example="(248) 721-4985"),
 *     @OA\Property(property="website", type="string", example="www.company.com"),
 *     @OA\Property(property="billing_phone", type="string", example="1555555555"),
 *     @OA\Property(property="billing_phone_name", type="string", example="John Doe"),
 *     @OA\Property(property="billing_phone_extension", type="string", example="3333"),
 *     @OA\Property(property="billing_phones", type="array", description="aditional phones",
 *         @OA\Items(ref="#/components/schemas/PhonesSettingsRaw")
 *     ),
 *     @OA\Property(property="billing_email", type="string", example="mail@server.net"),
 *     @OA\Property(property="billing_payment_details", type="string", example="Some payment details"),
 *     @OA\Property(property="billing_terms", type="string", example="Some carrier terms"),
 *     @OA\Property(property="ecommerce_company_name", type="string", example="ADESA LAS VEGAS"),
 *     @OA\Property(property="ecommerce_address", type="string", example="1395 E 4th St, Reno, NV 89512"),
 *     @OA\Property(property="ecommerce_city", type="string", example="Reno"),
 *     @OA\Property(property="ecommerce_state_id", type="integer", example=1),
 *     @OA\Property(property="ecommerce_zip", type="string", example="89512"),
 *     @OA\Property(property="ecommerce_phone_name", type="string", example="John Doe"),
 *     @OA\Property(property="ecommerce_phone", type="string", example="1555555555"),
 *     @OA\Property(property="ecommerce_phone_extension", type="string", example="234"),
 *     @OA\Property(property="ecommerce_phones", type="array", description="aditional phones",
 *         @OA\Items(ref="#/components/schemas/PhonesSettingsRaw")
 *     ),
 *     @OA\Property(property="ecommerce_email", type="string", example="jack@mail.com"),
 *     @OA\Property(property="ecommerce_fax", type="string", example="(248) 721-4985"),
 *     @OA\Property(property="ecommerce_website", type="string", example="www.company.com"),
 *     @OA\Property(property="ecommerce_billing_phone", type="string", example="1555555555"),
 *     @OA\Property(property="ecommerce_billing_phone_name", type="string", example="John Doe"),
 *     @OA\Property(property="ecommerce_billing_phone_extension", type="string", example="3333"),
 *     @OA\Property(property="ecommerce_billing_phones", type="array", description="aditional phones",
 *         @OA\Items(ref="#/components/schemas/PhonesSettingsRaw")
 *     ),
 *     @OA\Property(property="ecommerce_billing_email", type="string", example="mail@server.net"),
 *     @OA\Property(property="ecommerce_billing_payment_details", type="string", example="Some payment details"),
 *     @OA\Property(property="ecommerce_billing_terms", type="string", example="Some carrier terms"),
 *     @OA\Property(property="ecommerce_billing_payment_options", type="string", example="Some payment options"),
 * )
 *
 * @OA\Schema(schema="PhonesSettingsRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"number"},
 *          @OA\Property(property="name", type="string", description="Name", example="Walls"),
 *          @OA\Property(property="number", type="string", description="Phone number", example="16555555555"),
 *          @OA\Property(property="extension", type="string", description="Phone extension", example="4111"),
 *      )}
 *  )
 */

class SettingsInfoRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string'],
            'address' => ['required', 'string', 'max:191'],
            'city' => ['required', 'string', 'max:255'],
            'state_id' => ['required', 'integer', Rule::exists(State::TABLE, 'id')],
            'zip' => ['required', 'string', 'min:3', 'max:10'],
            'timezone' => ['required', 'string', Rule::in(resolve(TimezoneService::class)->getTimezonesArr()->pluck('timezone')->toArray())],
            'phone' => ['required', 'string', new PhoneRule(), 'max:191'],
            'phone_name' => ['nullable', 'string', 'max:255'],
            'phone_extension' => ['nullable', 'string', 'max:191'],
            'phones' => ['nullable', 'array', 'max:4'],
            'phones.*.name' => ['nullable', 'string', 'max:255'],
            'phones.*.number' => ['sometimes', 'string', new PhoneRule(), 'max:191'],
            'phones.*.extension' => ['nullable', 'string', 'max:191'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'fax' => ['nullable', 'string', 'max:191'],
            'website' => ['nullable', 'string', 'max:191'],
            'billing_phone' => ['required', new PhoneRule(), 'string', 'max:191'],
            'billing_phone_name' => ['nullable', 'string', 'max:191'],
            'billing_phone_extension' => ['nullable', 'string', 'max:191'],
            'billing_phones' => ['nullable', 'array', 'max:4'],
            'billing_phones.*.name' => ['nullable', 'string', 'max:255'],
            'billing_phones.*.number' => ['required', 'string', new PhoneRule(), 'max:191'],
            'billing_phones.*.extension' => ['nullable', 'string', 'max:191'],
            'billing_email' => ['nullable', 'email'],
            'billing_payment_details' => ['nullable', 'string'],
            'billing_terms' => ['nullable', 'string'],
            'ecommerce_company_name' => ['required', 'string'],
            'ecommerce_address' => ['required', 'string'],
            'ecommerce_city' => ['required', 'string'],
            'ecommerce_state_id' => ['required', 'integer', Rule::exists(State::TABLE, 'id')],
            'ecommerce_zip' => ['required', 'string'],
            'ecommerce_phone' => ['required', 'string', new PhoneRule(), 'max:191'],
            'ecommerce_phone_name' => ['nullable', 'string', 'max:255'],
            'ecommerce_phone_extension' => ['nullable', 'string', 'max:191'],
            'ecommerce_phones' => ['nullable', 'array', 'max:4'],
            'ecommerce_phones.*.name' => ['nullable', 'string', 'max:255'],
            'ecommerce_phones.*.number' => ['sometimes', 'string', new PhoneRule(), 'max:191'],
            'ecommerce_phones.*.extension' => ['nullable', 'string', 'max:191'],
            'ecommerce_email' => ['required', 'string', 'email', 'max:255'],
            'ecommerce_fax' => ['nullable', 'string', 'max:191'],
            'ecommerce_website' => ['nullable', 'string', 'max:191'],
            'ecommerce_billing_phone' => ['required', new PhoneRule(), 'string', 'max:191'],
            'ecommerce_billing_phone_name' => ['nullable', 'string', 'max:191'],
            'ecommerce_billing_phone_extension' => ['nullable', 'string', 'max:191'],
            'ecommerce_billing_phones' => ['nullable', 'array', 'max:4'],
            'ecommerce_billing_phones.*.name' => ['nullable', 'string', 'max:255'],
            'ecommerce_billing_phones.*.number' => ['required', 'string', new PhoneRule(), 'max:191'],
            'ecommerce_billing_phones.*.extension' => ['nullable', 'string', 'max:191'],
            'ecommerce_billing_email' => ['nullable', 'email'],
            'ecommerce_billing_payment_details' => ['nullable', 'string'],
            'ecommerce_billing_terms' => ['nullable', 'string'],
            'ecommerce_billing_payment_options' => ['nullable', 'string'],
        ];
    }
}

