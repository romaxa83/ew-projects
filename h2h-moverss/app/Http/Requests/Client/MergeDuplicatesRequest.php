<?php

namespace App\Http\Requests\Client;

use App\Models\Client\Email;
use App\Models\Client\Phone;
use Illuminate\Foundation\Http\FormRequest;

class MergeDuplicatesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'duplicates.*' => "required|exists:App\Models\Client,id",
//                'duplicates.*' => "required|exists:App\Models\Client,id",
//            'mergeBy.phones' => "required|array|min:1",
            'mergeBy.name.client_id' => "required|exists:App\Models\Client,id",
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // if one of Clients has emails
            if (empty($this->mergeBy['phones']) && $Phone = Phone::whereIn('client_id', $this->duplicates)->first('id')) {
                $validator->errors()->add('custom_phone',
                    'You should select at least 1 phone');

            }
            if (empty($this->mergeBy['emails']) && $Email = Email::whereIn('client_id', $this->duplicates)->first('id')) {
                $validator->errors()->add('custom_email',
                    'You should select at least 1 email');
            }

        });
    }

    public function messages()
    {
        return [
            'mergeBy.name.client_id.required' => 'You should select customer name!',
        ];
    }
}
