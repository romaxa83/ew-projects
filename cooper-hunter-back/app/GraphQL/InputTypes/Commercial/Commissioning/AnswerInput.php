<?php

namespace App\GraphQL\InputTypes\Commercial\Commissioning;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class AnswerInput extends BaseInputType
{
    public const NAME = 'CommissioningAnswerInput';

    public function fields(): array
    {
        return [
            'project_protocol_question_id' => [
                'type' => NonNullType::id(),
                ['required', 'int', Rule::exists(ProjectProtocolQuestion::class, 'id')]
            ],
            'text' => [
                'type' => Type::id(),
                ['nullable', 'string']
            ],
            'option_answer_ids' => [
                'type' => Type::listOf(Type::id()),
            ],
            'media' => [
                'type' => Type::listOf(FileType::Type()),
            ],
        ];
    }
}



