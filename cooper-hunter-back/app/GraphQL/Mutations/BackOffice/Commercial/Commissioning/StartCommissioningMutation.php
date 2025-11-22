<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProject;
use App\Permissions\Commercial\CommercialProjects\CommercialStartCommissioningPermission;
use App\Repositories\Commercial\CommercialProjectRepository;
use App\Services\Commercial\CommercialProjectService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class StartCommissioningMutation extends BaseMutation
{
    public const NAME = 'startCommissioning';
    public const PERMISSION = CommercialStartCommissioningPermission::KEY;

    public function __construct(
        protected CommercialProjectService $service,
        protected CommercialProjectRepository $repo,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(CommercialProject::class, 'id')],
                'description' => "CommercialProjectType - ID"
            ],
        ];
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
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
    ): ResponseMessageEntity
    {
        try {
            /** @var $model CommercialProject */
            $model = $this->repo->getByFields(['id' => $args['id']],[],true);

            makeTransaction(
                function () use ($model) {
                    $this->service->startPreCommissioning($model);
                }
            );

            return ResponseMessageEntity::success(__('messages.commercial.commissioning_start'));
        } catch (\Throwable $e){
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}



