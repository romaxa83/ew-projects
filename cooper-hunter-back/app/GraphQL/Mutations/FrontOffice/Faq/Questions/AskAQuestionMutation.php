<?php

namespace App\GraphQL\Mutations\FrontOffice\Faq\Questions;

use App\Dto\Faq\Questions\AskAQuestionDto;
use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\InputTypes\Faq\Questions\AskAQuestionInput;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\Services\Faq\QuestionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AskAQuestionMutation extends BaseMutation
{
    public const NAME = 'askAQuestion';

    public function __construct(protected QuestionService $service)
    {
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function args(): array
    {
        return [
            'input' => [
                'type' => AskAQuestionInput::nonNullType(),
            ],
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity {
        makeTransaction(
            fn() => $this->service->ask(AskAQuestionDto::byArgs($args['input']))
        );

        return ResponseMessageEntity::success(__('Thanks for the question. Our manager will contact you soon'));
    }
}
