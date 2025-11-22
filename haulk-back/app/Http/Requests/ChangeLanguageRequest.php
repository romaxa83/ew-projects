<?php


namespace App\Http\Requests;


use App\Models\Language;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeLanguageRequest extends FormRequest
{
    use OnlyValidateForm;
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'language' => ['required', 'string',  Rule::in(Language::pluck('slug')->toArray())]
        ];
    }
}