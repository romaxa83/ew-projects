<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\JsonRequest;
use App\Models\Client\{Email, Phone};

class ChangeClientRequest extends JsonRequest
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
            'order_id' => 'required|integer|exists:orders,id',
            'id' => 'nullable|integer|exists:clients,id',
            'name' => 'required|min:2|max:50',
            'lname' => 'nullable|max:80',
            'phone' => 'required_without:email|string',
            'email' => 'required_without:phone|nullable|email',
            'force' => 'nullable|boolean',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'First Name',
            'lname' => 'Last Name',
        ];
    }


    protected function prepareForValidation()
    {
        $this->sanitizeInput();

        return $this->getValidatorInstance();
    }

    private function sanitizeInput()
    {
        $input = $this->all();

        $input['name'] = strip_tags($input['name']);
        $input['lname'] = strip_tags($input['lname']);
        $input['phone'] = preg_replace('/[^0-9]/', '', $input['phone']);

        $this->replace($input);
    }


    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->id && empty($this->force)) {
                // Попытка найти существующего юзера с такими данными
                if ($u = Phone::whereValue($this->phone)->first('id')) {
                    $validator->errors()->add('custom_phone',
                        'Client ('.$u->id.') with phone number "'.$this->phone.'" already exists');
                }

                if ($u = Email::whereValue($this->email)->first()) {
                    $validator->errors()->add('custom_email',
                        'Client ('.$u->id.') with email "'.$this->email.'" already exists');
                }
            }
        });
    }
}
