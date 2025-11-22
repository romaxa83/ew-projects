<?php

namespace App\GraphQL\InputTypes\Dictionaries;

class ProblemInputType extends BaseDictionaryInputType
{
    public const NAME = 'ProblemInputType';

    protected string $translateInputTypeClass = ProblemTranslateInputType::class;
}
