<?php

namespace App\Http\Requests\Contacts;

use App\Models\Contacts\Contact;
use App\Traits\Requests\ContactTransformerTrait;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class IndexContactRequest extends FormRequest
{
    use ContactTransformerTrait;
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return $this->user()->can('viewList', Contact::class);
    }

    public function rules(): array
    {
        return [
            'order_by' => [
                'nullable',
                'in:id,full_name'
            ],
            'order_type' => [
                'nullable',
                'in:asc,desc'
            ],
            'per_page' => [
                'nullable',
                'integer'
            ],
            'name' => [
                'nullable',
                'string'
            ],
            'type_id' => [
                'nullable',
                'numeric'
            ]
        ];
    }

    public function validated(): array
    {
        $validated = parent::validated();

        data_fill($validated, 'order_by', 'full_name');
        data_fill($validated, 'order_type', 'asc');
        data_fill($validated, 'per_page', 10);

        return $validated;
    }
}
