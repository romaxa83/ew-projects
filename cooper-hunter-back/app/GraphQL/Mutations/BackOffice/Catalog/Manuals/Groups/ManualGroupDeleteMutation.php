<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Manuals\Groups;

use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Permissions\Catalog\Manuals\ManualDeletePermission;
use App\Services\Catalog\Manuals\ManualGroupService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ManualGroupDeleteMutation extends BaseMutation
{
    public const NAME = 'manualGroupDelete';
    public const PERMISSION = ManualDeletePermission::KEY;

    public function __construct(protected ManualGroupService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ]
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /** @throws Throwable */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                ManualGroup::query()->findOrFail($args['id'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(ManualGroup::TABLE, 'id')],
        ];
    }
}
