<?php

namespace App\Http\Requests\News;

use App\Models\News\News;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class NewsRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('news create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title_en' => ['required', 'string', 'max:255'],
            'title_ru' => ['nullable', 'string', 'max:255'],
            'title_es' => ['nullable', 'string', 'max:255'],
            'body_short_en' => ['nullable'],
            'body_short_ru' => ['nullable'],
            'body_short_es' => ['nullable'],
            'body_en' => ['nullable'],
            'body_ru' => ['nullable'],
            'body_es' => ['nullable'],
            'sticky' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
            News::NEWS_PHOTO_FIELD_NAME => ['nullable', 'file'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'sticky' => $this->boolean('sticky'),
            'status' => $this->boolean('status'),
        ]);
    }
}
