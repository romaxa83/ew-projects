<?php

namespace App\Http\Requests\Example;

use App\Http\Requests\JsonRequest;

class SaveCompanyRequest extends JsonRequest
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
        $rules = [
            'active' => 'boolean',
            'title' => 'required|min:2|max:50',
            'description' => 'required|max:500',
        ];

        if ($this->isMethod('post')) {
            $rules['id'] = 'required|numeric';
            $rules['file'] = 'file|image';
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->sanitizeInput();

        return $this->getValidatorInstance();
    }

    private function sanitizeInput()
    {
        $input = $this->all();

        $input['title'] = strip_tags($input['title']);
        $input['description'] = strip_tags($input['description'], '<br/>');

        $this->replace($input);
    }
}
