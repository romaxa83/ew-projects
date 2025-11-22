<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\ProblemTranslate;

class ProblemTranslateType extends BaseDictionaryTranslateType
{
    public const NAME = 'ProblemTranslateType';
    public const MODEL = ProblemTranslate::class;
}
