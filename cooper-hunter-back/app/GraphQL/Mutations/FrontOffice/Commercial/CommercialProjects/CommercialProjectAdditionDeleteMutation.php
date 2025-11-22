<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProjectAddition;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectDeletePermission;
use App\Repositories\Commercial\CommercialProjectAdditionRepository;
use App\Services\Commercial\CommercialProjectAdditionService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialProjectAdditionDeleteMutation extends BaseMutation
{
    public const NAME = 'commercialProjectAdditionalDelete';
    public const PERMISSION = CommercialProjectDeletePermission::KEY;

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
            ]
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
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
    ): bool
    {
        $this->isTechnicianCommercial();

        /** @var $model CommercialProjectAddition */
        $model = $this->repo->getBy('id', $args['id']);

        if($model->project->warranty){
            throw new TranslatedException(__('exceptions.commercial.addition.can\'t delete'), 502);
        }

        return makeTransaction(
            fn() => $this->service->delete($model)
        );
    }
}



