<?php

namespace App\Http\Requests\Saas\Companies;

use App\Permissions\Saas\Companies\CompanyDelete;
use App\Rules\Saas\Company\IsNotActive;
use Illuminate\Foundation\Http\FormRequest;

class DestroyNoteRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can(CompanyDelete::KEY, $this->route()->parameter('company'));
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'company' => $this->route()->parameter('company')
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'company' => [new IsNotActive()]
        ];
    }
}
