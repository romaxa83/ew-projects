<?php

namespace App\Traits;

trait ValidationRulesTrait
{

    public function generateRules(array $oneLanguageRules = [], array $multipleLanguageRules = []): array
    {
        $rules = $oneLanguageRules;
        $translatesRules = $multipleLanguageRules;
        foreach ($translatesRules as $key => $localRules) {
            foreach (config('languages', []) as $language) {
                $rules[$language['slug'] . '.' . $key] = $localRules;
            }
        }
        return $rules;
    }

    public function email(): string
    {
        return 'regex:/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/';
    }

    public static function USAPhone(): string
    {
        // return 'regex:/^(?:(?:\((?=\d{3}\)))?(\d{3})(?:(?<=\(\d{3})\))?[\s.\/-]?)?(\d{3})[\s\.\/-]?(\d{4})\s?(?:(?:(?:(?:e|x|ex|ext)\.?|extension)\s?)(?=\d+)(\d+))?$/x';
        return 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/';
    }

    public function orderAttachmentTypes(): string
    {
        return 'mimes:pdf,png,jpg,jpeg,jpe,doc,docx,txt,xls,xlsx';
    }

    public function dateFormat(): string
    {
        return 'date_format:' . config('formats.date');
    }

    public function datetimeFormat(): string
    {
        return 'date_format:' . config('formats.datetime');
    }

    public function passwordRule(): string
    {
        return 'regex:/^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*)[0-9a-zA-Z]{8,}$/';
    }
}
