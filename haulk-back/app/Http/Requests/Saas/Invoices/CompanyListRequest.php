<?php

namespace App\Http\Requests\Saas\Invoices;

use App\Permissions\Saas\Invoices\InvoiceList;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class CompanyListRequest extends FormRequest
{

    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can(InvoiceList::KEY);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'company_name' => [
                'nullable',
                'string',
                'min:2',
                'max:255'
            ]
        ];
    }
}
