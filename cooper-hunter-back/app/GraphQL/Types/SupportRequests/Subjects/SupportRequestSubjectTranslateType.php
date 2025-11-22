<?php

namespace App\GraphQL\Types\SupportRequests\Subjects;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Support\RequestSubjects\SupportRequestSubjectTranslation;
use GraphQL\Type\Definition\Type;

class SupportRequestSubjectTranslateType extends BaseTranslationType
{
    public const NAME = 'SupportRequestSubjectTranslateType';
    public const MODEL = SupportRequestSubjectTranslation::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'title' => [
                    'type' => NonNullType::string(),
                ],
                'slug' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
                    'type' => Type::string(),
                ],
            ]
        );
    }
}
