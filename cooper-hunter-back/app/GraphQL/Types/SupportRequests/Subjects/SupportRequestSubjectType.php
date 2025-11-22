<?php

namespace App\GraphQL\Types\SupportRequests\Subjects;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Support\RequestSubjects\SupportRequestSubject;

class SupportRequestSubjectType extends BaseType
{
    public const NAME = 'SupportRequestSubjectType';
    public const MODEL = SupportRequestSubject::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'active' => [
                    'type' => NonNullType::boolean()
                ],
                'translation' => [
                    'type' => SupportRequestSubjectTranslateType::nonNullType(),
                    'is_relation' => true,
                ],
                'translations' => [
                    'type' => SupportRequestSubjectTranslateType::nonNullList(),
                    'is_relation' => true,
                ],
            ]
        );
    }


}
