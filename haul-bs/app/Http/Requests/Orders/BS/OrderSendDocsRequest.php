<?php

namespace App\Http\Requests\Orders\BS;

use App\Dto\Orders\BS\SendDocsDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="OrderSendDocsRequest",
 *     required={"recipient_email", "content", "invoice_date"},
 *     @OA\Property(property="recipient_email", description="Array of recipient emails", type="array", nullable=false,
 *         @OA\Items(type="string", example="my.email@gmail.com")
 *     ),
 *     @OA\Property(property="content", description="Selected sending docs", type="array", nullable=false,
 *         @OA\Items (type="string", enum={"invoice"})
 *     ),
 *     @OA\Property(property="invoice_date", type="string", nullable=true, example="12/21/2021",
 *         description="Invoice date (required if 'content' contains 'invoice'). Format m/d/Y"
 *     ),
 *  )
 *
 * @property array send_via
 * @property array content
 * @property array recipient_email
 */
class OrderSendDocsRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        $content = $this->input('content');
        return [
            'recipient_email' => ['array', 'required', 'max:5'],
            'recipient_email.*' => ['required', 'email'],
            'invoice_date' => [
                Rule::requiredIf(
                    fn() => is_array($content) && in_array('invoice', $content, true)
                ),
                'nullable',
                'date_format:m/d/Y',
            ],
            'content' => ['required', 'array'],
            'content.*' => ['required', 'string', 'in:invoice',],
        ];
    }

    public function dto(): SendDocsDto
    {
        return SendDocsDto::create()->origin($this->validated());
    }
}
