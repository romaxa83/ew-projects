<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Labels;

use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Videos\Group;
use App\Permissions\Catalog\Labels\DeletePermission;
use App\Repositories\Catalog\Labels\LabelRepository;
use App\Services\Catalog\Labels\LabelService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DeleteMutation extends BaseMutation
{
    public const NAME = 'labelDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(
        protected LabelService $service,
        protected LabelRepository $repo,
    )
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'id' => ['type' => NonNullType::id()],
        ];
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Group
     * @throws Throwable
     */
    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        /** @var $model Label */
        $model = $this->repo->getBy('id', $args['id'], withException:true);

        return makeTransaction(
            fn(): bool => $this->service->delete($model)
        );
    }
}
