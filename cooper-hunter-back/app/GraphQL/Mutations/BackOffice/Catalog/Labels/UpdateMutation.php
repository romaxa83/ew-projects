<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Labels;

use App\Dto\Catalog\Labels\LabelDto;
use App\GraphQL\InputTypes\Catalog\Labels\LabelInput;
use App\GraphQL\Types\Catalog\Labels\LabelType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Videos\Group;
use App\Permissions\Catalog\Labels\UpdatePermission;
use App\Repositories\Catalog\Labels\LabelRepository;
use App\Services\Catalog\Labels\LabelService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UpdateMutation extends BaseMutation
{
    public const NAME = 'labelUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected LabelService $service,
        protected LabelRepository $repo,
    )
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return LabelType::type();
    }

    public function args(): array
    {
        return [
            'id' => ['type' => NonNullType::id()],
            'input' => LabelInput::nonNullType(),
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
    ): Label
    {
        /** @var $model Label */
        $model = $this->repo->getBy('id', $args['id'], withException:true);

        return makeTransaction(
            fn(): Label => $this->service->update(
                $model,
                LabelDto::byArgs($args['input'])
            )
        );
    }
}

