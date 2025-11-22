<?php

namespace App\Http\Requests\QuestionAnswer;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class QuestionAnswerRequest extends FormRequest
{
    use OnlyValidateForm;

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
            'question_en' => 'required|string|min:2|max:255',
            'answer_en' => 'required|string|min:2',
            'question_es' => 'nullable|string|min:2|max:255',
            'answer_es' => 'nullable|string|min:2',
            'question_ru' => 'nullable|string|min:2|max:255',
            'answer_ru' => 'nullable|string|min:2'
        ];
    }
}
