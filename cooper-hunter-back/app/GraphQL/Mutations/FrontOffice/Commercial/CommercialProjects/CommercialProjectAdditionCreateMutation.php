<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\Dto\Commercial\CommercialProjectAdditionDto;
use App\GraphQL\InputTypes\Commercial\CommercialProjectAdditionInput;
use App\GraphQL\Types\Commercial\CommercialProjectAdditionType;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectAddition;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectCreatePermission;
use App\Repositories\Commercial\CommercialProjectRepository;
use App\Services\Commercial\CommercialProjectAdditionService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialProjectAdditionCreateMutation extends BaseMutation
{
    public const NAME = 'commercialProjectAdditionalCreate';
    public const PERMISSION = CommercialProjectCreatePermission::KEY;

    public function __construct(
        protected CommercialProjectAdditionService $service,
        protected CommercialProjectRepository $repo,
    )
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
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

        /** @var $project CommercialProject */
        $project = $this->repo->getBy('id', $dto->commercialProjectID);
        if($project->additions){
            throw new TranslatedException(__('exceptions.commercial.addition.exist'), 502);
        }

        return makeTransaction(
            fn() => $this->service->create($dto)
        );
    }
}

