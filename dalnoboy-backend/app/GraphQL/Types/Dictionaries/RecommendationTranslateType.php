<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\RecommendationTranslate;

class RecommendationTranslateType extends BaseDictionaryTranslateType
{
    public const NAME = 'RecommendationTranslateType';
    public const MODEL = RecommendationTranslate::class;
}
