<?php

namespace App\Http\Requests\Orders;

use App\Dto\Orders\SendDocsDto;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendDocsMobileRequest extends FormRequest
{
    use OnlyValidateForm;

    public function authorize(): bool
    {
        return $this->user()->can('orders inspection') && $this->user()->can('viewAssignedToMe', $this->route()->parameter('order'));
    }

    public function rules(): array
    {
        return [
            'recipient_email' => [
                'required',
                'email'
            ],
            'content' => [
                'required',
                'string',
                Rule::in(['invoice', 'bol', 'both'])
            ],
            'after_inspection' => [
                'nullable',
                'string',
                Rule::in([
                    'pickup',
                    'delivery'
                ])
            ]
        ];
    }

    public function afterInspection(): ?string
    {
        if (array_key_exists('after_inspection', $this->validated())) {
            return $this->validated()['after_inspection'];
        }
        return null;
    }

    public function dto(): SendDocsDto
    {
        return SendDocsDto::create()->mobileOrigin($this->validated(), $this->order);
    }
}
