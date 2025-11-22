<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\InspectionReasonTranslate;

class InspectionReasonTranslateType extends BaseDictionaryTranslateType
{
    public const NAME = 'InspectionReasonTranslateType';
    public const MODEL = InspectionReasonTranslate::class;
}
