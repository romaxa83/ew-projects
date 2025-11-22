<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\ProtocolStatusEnumType;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use Core\Traits\Auth\AuthGuardsTrait;
use GraphQL\Type\Definition\Type;

class ProjectProtocolType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'ProjectProtocolType';
    public const MODEL = ProjectProtocol::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'status' => [
                    'type' => ProtocolStatusEnumType::nonNullType(),
                ],
                'protocol' => [
                    'type' => ProtocolType::Type(),
                    'is_relation' => true,
                    'always' => ['id', 'protocol_id']
                ],
                'project_questions' => [
                    'type' => ProjectProtocolQuestionType::list(),
                    'is_relation' => true,
                    'alias' => 'projectQuestions',
                ],
                'closed_at' => [
                    'type' => Type::string(),
                    'resolve' => static fn(ProjectProtocol $p): ?string => $p->closed_at?->format(
                        DatetimeEnum::DEFAULT_FORMAT
                    ),
                    'description' => 'Value in '.DatetimeEnum::DEFAULT_FORMAT.' format',
                ],
                'total_questions' => [
                    'type' => Type::int(),
                    'resolve' => static fn (ProjectProtocol $model) => $model->total_questions
                ],
                'total_correct_answers' => [
                    'type' => Type::int(),
                    'resolve' => static fn (ProjectProtocol $model) => $model->total_correct_answers
                ],
                'total_wrong_answers' => [
                    'type' => Type::int(),
                    'resolve' => static fn (ProjectProtocol $model) => $model->total_wrong_answers
                ],
            ],
        );
    }
}



