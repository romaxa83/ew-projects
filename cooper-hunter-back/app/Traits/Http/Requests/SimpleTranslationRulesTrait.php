<?php

namespace App\Traits\Http\Requests;

use App\Models\Localization\Language;
use App\Rules\TranslationsArrayValidator;
use Illuminate\Validation\Rule;

trait SimpleTranslationRulesTrait
{
    protected string $translationField = 'translations';

    public function getTranslationRules(): array
    {
        $translationField = $this->translationField;

        $translations = [
            $translationField => [new TranslationsArrayValidator()],
        ];

        foreach ($this->getBaseTranslationFields() as $field => $rules) {
            $translations[$translationField . '.*.' . $field] = $rules;
        }

        return $translations;
    }

    protected function getBaseTranslationFields(): array
    {
        return array_merge(
            $this->getTranslationFields(),
            [
                'language' => ['required', 'string', 'distinct', Rule::exists(Language::TABLE, 'slug')],
            ],
        );
    }

    protected function getTranslationFields(): array
    {
        return [
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string']
        ];
    }
}
