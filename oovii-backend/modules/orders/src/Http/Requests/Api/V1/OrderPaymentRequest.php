<?php

namespace WezomCms\Orders\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Orders\Models\OrderPaymentInformation;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Order payment request",
 *     required={"orders"}
 * )
 */
class OrderPaymentRequest extends FormRequest
{
    private ?array $paymentRules = null;

    public function rules(): array
    {
        $rules = [];

        /** @var OrderPaymentInformation $paymentInfo */
        $paymentInfo = $this->route('paymentInfo');

        if ($driver = $paymentInfo->getPaymentDriver()) {
            $this->paymentRules = $driver->getValidationRules();
        }

        if ($this->paymentRules) {
            $rules['payment_data'] = ['required', 'array'];
            foreach ($this->paymentRules[0] as $attribute => $rule) {
                $rules['payment_data.' . $attribute] = $rule;
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];

        if ($this->paymentRules && count($this->paymentRules) >= 3) {
            foreach ($this->paymentRules[2] as $attribute => $translation) {
                $attributes['payment_data.' . $attribute] = $translation;
            }
        }

        return $attributes;
    }

    /**

     */
}
