<?php

namespace App\Http\Requests\Carrier;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class SendDestroyNoteRequest extends FormRequest
{

    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('company-settings delete') && $this->user()->can('billing update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    public function validated(): array
    {
        return [
            'company' => $this->user()->getCompany()
        ];

    }
}
