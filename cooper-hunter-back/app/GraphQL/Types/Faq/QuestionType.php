<?php

namespace App\GraphQL\Types\Faq;

use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Faq\Questions\QuestionStatusEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Faq\Question;
use GraphQL\Type\Definition\Type;

class QuestionType extends BaseType
{
    public const NAME = 'QuestionType';
    public const MODEL = Question::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'admin' => [
                    'type' => AdminType::type(),
                    'description' => 'The admin who replied to the message',
                ],
                'status' => [
                    'type' => QuestionStatusEnumType::nonNullType(),
                ],
                'name' => [
                    'type' => NonNullType::string(),
                ],
                'email' => [
                    'type' => NonNullType::string(),
                ],
                'question' => [
                    'type' => NonNullType::string(),
                ],
                'answer' => [
                    'type' => Type::string(),
                    'description' => 'Reply by admin',
                ],
            ]
        );
    }
}
