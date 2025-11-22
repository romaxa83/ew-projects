<?php

namespace App\Http\Requests\Saas\Companies;

use App\Models\Saas\Company\Company;
use App\Permissions\Saas\Companies\CompanyDelete;
use App\Rules\Saas\Company\CheckDestroyToken;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class DestroyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can(CompanyDelete::KEY);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => [
                'required',
                'in:confirm,decline'
            ],
            'token' => [
                'required',
                'string',
                new CheckDestroyToken($this->type)
            ]
        ];
    }

    /**
     * @return array
     * @throws ValidationException
     */
    public function validated(): array
    {
        return array_merge(
            $this->validator->validated(),
            [
                'company' => Company::filter(['destroy' => ['type' => $this->type, 'value' => $this->token]])->first()
            ]
        );
    }

}
