<?php

namespace App\Http\Requests\Translates;

use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Route;

class TranslateRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $translate = Route::getCurrentRoute()->parameter('translate');
        $rules = [];
        if ($translate) {
            $rules['key'] = ['required', 'string', 'max:191', 'unique:translates,key,' . $translate->id];
        } else {
            $rules['key'] = ['required', 'string', 'max:191', 'unique:translates,key'];
        }
        return $this->generateRules(
            $rules,
            [
                'text' => ['nullable', 'string'],
            ]
        );
    }

}
