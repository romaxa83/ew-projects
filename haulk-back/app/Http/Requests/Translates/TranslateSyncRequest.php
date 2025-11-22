<?php

namespace App\Http\Requests\Translates;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property array $translates
 */
class TranslateSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'translates' => ['required', 'array'],
            'translates.*.key' => ['required', 'string'],
        ];

        foreach (config('languages', []) as $language) {
            $rules["translates.*.{$language['slug']}.text"] = ['nullable', 'string'];
        }

        return $rules;
    }
}
