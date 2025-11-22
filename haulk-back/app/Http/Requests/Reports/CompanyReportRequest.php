<?php

namespace App\Http\Requests\Reports;

use App\Documents\Filters\OrderDocumentFilter;
use App\Dto\Reports\CompanyReportDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('company-reports read');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'payment_status' => [
                'nullable',
                'string',
                Rule::in(OrderDocumentFilter::PAYMENT_STATUSES)
            ],
            'invoice_from' => [
                'nullable',
                'date',
                'required_with:invoice_to'
            ],
            'invoice_to' => [
                'nullable',
                'date',
                'required_with:invoice_from'
            ],
            'company_name' => [
                'nullable',
                'string'
            ],
            'invoice_id' => [
                'nullable',
                'string'
            ],
            'check_id' => [
                'nullable',
                'string'
            ],
            'payment_method_id' => [
                'nullable',
                'integer'
            ],
            'page' => [
                'nullable',
                'integer'
            ],
            'per_page' => [
                'nullable',
                'integer'
            ],
            'order_by' => [
                'nullable',
                'string',
                Rule::in([
                    'company_name',
                    'total_count',
                    'last_payment_stage',
                    'total_due_count',
                    'past_due_count',
                    'total_due',
                    'past_due',
                    'current_due'
                ])
            ],
            'order_type' => [
                'nullable',
                'string',
                Rule::in([
                    'asc',
                    'desc'
                ])
            ]
        ];
    }

    public function dto(): CompanyReportDto
    {
        return CompanyReportDto::create($this->validated());
    }
}
