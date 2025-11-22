<?php

namespace App\Http\Requests\Saas\Support\Backoffice;

use App\Models\Saas\Support\SupportRequest;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class SetLabelRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('setLabel', $this->route()->parameter('supportRequest'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'label' => [
                'required',
                'integer',
                'in:' . implode(
                    ',',
                    array_merge(
                        [SupportRequest::REMOVE_LABEL],
                        array_keys(SupportRequest::LABELS_DESCRIPTION)
                    )
                )
            ]
        ];
    }
}
