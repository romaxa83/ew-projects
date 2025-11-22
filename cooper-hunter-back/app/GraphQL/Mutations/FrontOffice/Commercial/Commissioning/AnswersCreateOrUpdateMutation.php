<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\Commissioning;

use App\Dto\Commercial\Commissioning\AnswersDto;
use App\Exceptions\Commercial\Commissioning\ValidateException;
use App\GraphQL\InputTypes\Commercial\Commissioning\AnswerInput;
use App\GraphQL\Types\Commercial\Commissioning\ProjectProtocolType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Permissions\Commercial\Commissionings\Answer\CreatePermission;
use App\Repositories\Commercial\Commissioning\ProjectProtocolRepository;
use App\Services\Commercial\Commissioning\AnswerService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Error\ValidationError;
use Rebing\GraphQL\Support\SelectFields;

class AnswersCreateOrUpdateMutation extends BaseMutation
{
    public const NAME = 'commissioningAnswersCreateOrUpdate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(
        private AnswerService $service,
        protected ProjectProtocolRepository $protocolRepository
    )
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'project_protocol_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(ProjectProtocol::class, 'id')],
            ],
            'input' => [
                'type' => AnswerInput::nonNullList(),
            ]
        ];
    }

    public function type(): Type
    {
        return ProjectProtocolType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ProjectProtocol
    {
        $this->isTechnicianCommercial();

        $dto = AnswersDto::byArgs($args['input']);
        /** @var $projectProtocol ProjectProtocol */
        $projectProtocol = $this->protocolRepository->getByFields(
            ['id' => $args['project_protocol_id']],
            ['projectQuestions']
        );

        $this->checkProjectProtocol($projectProtocol);

        try {
            makeTransaction(
                fn() => $this->service->createOrUpdate($dto, $projectProtocol)
            );

            return $projectProtocol->refresh();
        }
        catch (ValidateException $e) {
            $validator = validator([], []); // Empty data and rules fields
            $validator->errors()->add($e->getField(), $e->getMessage());

            throw new ValidationError('validation', $validator);
        }
        catch (\Exception $e){
            throw new TranslatedException($e->getMessage(), 502);
        }
    }

    private function checkProjectProtocol(ProjectProtocol $projectProtocol): void
    {
        if($projectProtocol->protocol->isCommissioning() && !$projectProtocol->project->isStartCommissioning()){
            throw new TranslatedException(__('exceptions.commercial.commissioning.commissioning not started yet'), 502);
        }

        if($projectProtocol->project->isEndCommissioning()){
            throw new TranslatedException(__('exceptions.commercial.commissioning.commissioning for this project is closed'), 502);
        }

        if($projectProtocol->isDone()){
            throw new TranslatedException(__('exceptions.commercial.commissioning.this protocol is closed'), 502);
        }
    }
}
