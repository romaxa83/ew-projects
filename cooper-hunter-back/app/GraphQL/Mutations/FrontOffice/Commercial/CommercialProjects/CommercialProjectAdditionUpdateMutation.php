<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\Dto\Commercial\CommercialProjectAdditionDto;
use App\GraphQL\InputTypes\Commercial\CommercialProjectAdditionInput;
use App\GraphQL\Types\Commercial\CommercialProjectAdditionType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProjectAddition;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectUpdatePermission;
use App\Repositories\Commercial\CommercialProjectAdditionRepository;
use App\Services\Commercial\CommercialProjectAdditionService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialProjectAdditionUpdateMutation extends BaseMutation
{
    public const NAME = 'commercialProjectAdditionalUpdate';
    public const PERMISSION = CommercialProjectUpdatePermission::KEY;

    public function __construct(
        protected CommercialProjectAdditionService $service,
        protected CommercialProjectAdditionRepository $repo,
    )
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(CommercialProjectAddition::class, 'id')],
                'description' => "CommercialProjectAdditionType - ID"
            ],
            'input' => [
                'type' => CommercialProjectAdditionInput::nonNullType(),
            ]
        ];
    }

    public function type(): Type
    {
        return CommercialProjectAdditionType::nonNullType();
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
    ): CommercialProjectAddition
    {
        $this->isTechnicianCommercial();
        $dto = CommercialProjectAdditionDto::byArgs($args['input']);

        /** @var $model CommercialProjectAddition */
        $model = $this->repo->getBy('id', $args['id']);

        if($model->project->warranty){
            throw new TranslatedException(__('exceptions.commercial.addition.can\'t update'), 502);
        }

        return makeTransaction(
            fn() => $this->service->update($model, $dto)
        );
    }
}


