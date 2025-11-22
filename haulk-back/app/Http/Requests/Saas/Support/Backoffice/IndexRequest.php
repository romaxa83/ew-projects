<?php

namespace App\Http\Requests\Saas\Support\Backoffice;

use App\Models\Saas\Support\SupportRequest;
use App\Permissions\Saas\Support\SupportRequestList;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can(SupportRequestList::KEY);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_by' => !empty($this['order_by']) ? $this['order_by'] : 'created_at',
            'order_type' => !empty($this['order_type']) ? $this['order_type'] : 'desc',
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
            'order_by' => [
                'in:created_at,updated_at'
            ],
            'order_type' => [
                'in:asc,desc'
            ],
            'per_page' => [
                'nullable',
                'integer'
            ],
            'page' => [
                'nullable',
                'integer'
            ],
            'admin' => [
                'nullable',
                'integer',
                'exists:admins,id'
            ],
            'status' => [
                'nullable',
                'integer',
                'in:' . implode(',', array_keys(SupportRequest::STATUSES_DESCRIPTION))
            ],
            'label' => [
                'nullable',
                'integer',
                'in:' . implode(',', array_keys(SupportRequest::LABELS_DESCRIPTION))
            ],
            'source' => [
                'nullable',
                'integer',
                'in:' . implode(',', array_keys(SupportRequest::SOURCES_DESCRIPTION))
            ],
            'date_from' => [
                'nullable',
                'date',
                'date_format:m/d/Y'
            ],
            'date_to' => [
                'nullable',
                'date',
                'date_format:m/d/Y'
            ]
        ];
    }
}
