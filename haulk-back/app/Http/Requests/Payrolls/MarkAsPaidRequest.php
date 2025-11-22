<?php

namespace App\Http\Requests\Payrolls;

use App\Models\Payrolls\Payroll;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class MarkAsPaidRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('payrolls update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'array'],
            'id.*' => ['required', 'integer', 'exists:' . Payroll::class . ',id'],
        ];
    }

    public function payrolls(): array
    {
        return $this->validated()['id'];
    }
}
