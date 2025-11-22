<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Features\Values;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Value;
use App\Permissions\Catalog\Features\Values\DeletePermission;
use App\Repositories\Catalog\ValueRepository;
use App\Services\Catalog\ValueService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class FeatureValueDeleteMutation extends BaseMutation
{
    public const NAME = 'featureValueDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(
        protected ValueService $service,
        protected ValueRepository $repo
    ) {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
        ];
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity {
        try {
            /** @var $model Value */
            $model = $this->repo->getByFields(['id' => $args['id']]);
            $this->service->delete($model);

            return ResponseMessageEntity::success(
                __('messages.catalog.feature.value.actions.delete.success.one-entity')
            );
        } catch (Throwable) {
            return ResponseMessageEntity::fail(__('Oops, something went wrong!'));
        }
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:catalog_feature_values,id'],
        ];
    }
}


