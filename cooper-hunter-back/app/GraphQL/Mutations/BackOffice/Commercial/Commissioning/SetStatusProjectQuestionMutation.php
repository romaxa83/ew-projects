<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\GraphQL\Types\Commercial\Commissioning\ProjectProtocolQuestionType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerStatusEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use App\Permissions\Commercial\Commissionings\Question\UpdatePermission;
use App\Repositories\Commercial\Commissioning\ProjectProtocolQuestionRepository;
use App\Services\Commercial\Commissioning\ProjectProtocolQuestionService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SetStatusProjectQuestionMutation extends BaseMutation
{
    public const NAME = 'commissioningSetStatusProjectQuestion';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected ProjectProtocolQuestionService $service,
        protected ProjectProtocolQuestionRepository $repo,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'project_protocol_question_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(ProjectProtocolQuestion::class, 'id')],
                'description' => "ProjectProtocolQuestionType - ID"
            ],
            'status' => [
                'type' => AnswerStatusEnumType::nonNullType(),
                'rules' => ['required', 'string', rule_in(AnswerStatus::ACCEPT, AnswerStatus::REJECT)],
                'description' => "status for change"
            ],
        ];
    }

    public function type(): Type
    {
        return ProjectProtocolQuestionType::nonNullType();
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
    ): ProjectProtocolQuestion
    {
        /** @var $projectProtocolQuestion ProjectProtocolQuestion */
        $projectProtocolQuestion = $this->repo->getByFields(['id' => $args['project_protocol_question_id']]);

        if(null === $projectProtocolQuestion->answer){
            throw new TranslatedException(__('exceptions.commercial.commissioning.question does not contain an answer'), 502);
        }

        if($projectProtocolQuestion->projectProtocol->isDone()){
            throw new TranslatedException(__('exceptions.commercial.commissioning.this protocol is closed'), 502);
        }

        $model = makeTransaction(
            fn(): ProjectProtocolQuestion => $this->service->setStatus($projectProtocolQuestion, $args['status'])
        );

        return $model;
    }
}


