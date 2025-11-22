<?php

namespace App\Http\Requests\BodyShop\Orders;

use App\Dto\BodyShop\Orders\SendDocsDto;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property array send_via
 * @property array content
 * @property array recipient_email
 */
class SendDocsRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        $request = $this;

        $content = $this->input('content');
        return [
            'recipient_email' => [
                'array',
                'required'
            ],
            'recipient_email.*' => [
                'required',
                'email'
            ],
            'invoice_date' => [
                Rule::requiredIf(
                    fn() => is_array($content) && in_array('invoice', $content, true)
                ),
                'nullable',
                'date_format:m/d/Y',
            ],
            'content' => [
                'required',
                'array'
            ],
            'content.*' => [
                'required',
                'string',
                'in:invoice',
            ],
        ];
    }

    public function dto(): SendDocsDto
    {
        return SendDocsDto::create()->origin($this->validated());
    }
}
