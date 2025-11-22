<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Manuals;

use App\GraphQL\Types\Catalog\Manuals\ManualType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Permissions\Catalog\Manuals\ManualListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class ManualsQuery extends BaseQuery
{
    public const NAME = 'manuals';
    public const PERMISSION = ManualListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ManualType::list();
    }

    public function args(): array
    {
        return [
            'manual_group_id' => [
                'type' => NonNullType::id(),
            ],
            'manual_id' => [
                'type' => Type::id(),
            ],
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return Manual::query()
            ->where('manual_group_id', $args['manual_group_id'])
            ->when($id = $args['manual_id'] ?? null, fn(Builder $b) => $b->whereKey($id))
            ->get();
    }

    protected function rules(array $args = []): array
    {
        return [
            'manual_group_id' => ['required', 'integer', Rule::exists(ManualGroup::TABLE, 'id')],
            'manual_id' => [
                'nullable',
                'integer',
                Rule::exists(Manual::TABLE, 'id')
                    ->where('manual_group_id', $args['manual_group_id'])
            ],
        ];
    }
}
