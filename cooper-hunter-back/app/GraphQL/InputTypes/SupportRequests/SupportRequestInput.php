<?php

namespace App\GraphQL\InputTypes\SupportRequests;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use Illuminate\Validation\Rule;

class SupportRequestInput extends BaseInputType
{
    public const NAME = 'SupportRequestInput';

    public function fields(): array
    {
        return [
            'subject_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    Rule::exists(SupportRequestSubject::class, 'id')
                        ->where('active', true),
                ],
            ],
            'message' => [
                'type' => SupportRequestMessageInput::nonNullType(),
            ],
        ];
    }
}
