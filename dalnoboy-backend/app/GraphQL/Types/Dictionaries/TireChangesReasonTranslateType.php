<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\TireChangesReasonTranslate;

class TireChangesReasonTranslateType extends BaseDictionaryTranslateType
{
    public const NAME = 'TireChangesReasonTranslateType';
    public const MODEL = TireChangesReasonTranslate::class;
}
