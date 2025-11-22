<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Manuals;

use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Manuals\Manual;
use App\Permissions\Catalog\Manuals\ManualDeletePermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ManualDeleteMutation extends BaseManualMutation
{
    public const NAME = 'manualDelete';
    public const PERMISSION = ManualDeletePermission::KEY;

    public function args(): array
    {
        return [
            'manual_id' => [
                'type' => NonNullType::id(),
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                Manual::query()->findOrFail($args['manual_id']),
            ),
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'manual_id' => ['required', 'integer', Rule::exists(Manual::TABLE, 'id')],
        ];
    }
}
