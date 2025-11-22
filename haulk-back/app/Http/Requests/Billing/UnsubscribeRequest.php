<?php

namespace App\Http\Requests\Billing;

use App\Rules\Billing\isSubscriptionCompany;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class UnsubscribeRequest extends FormRequest
{

    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('billing update');
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'company' => $this->user()->getCompany()
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'company' => [
                new isSubscriptionCompany()
            ]
        ];
    }
}
