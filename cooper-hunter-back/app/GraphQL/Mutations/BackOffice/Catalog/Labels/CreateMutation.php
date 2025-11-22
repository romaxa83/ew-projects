<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Labels;

use App\Dto\Catalog\Labels\LabelDto;
use App\GraphQL\InputTypes\Catalog\Labels\LabelInput;
use App\GraphQL\Types\Catalog\Labels\LabelType;
use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Videos\Group;
use App\Permissions\Catalog\Labels\CreatePermission;
use App\Services\Catalog\Labels\LabelService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CreateMutation extends BaseMutation
{
    public const NAME = 'labelCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(protected LabelService $service)
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
        return makeTransaction(
            fn(): Label => $this->service->create(
                LabelDto::byArgs($args['input'])
            )
        );
    }
}
