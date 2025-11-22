<?php

namespace App\GraphQL\Queries\Common\Catalog\Categories;

use App\Models\Catalog\Categories\Category;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseCategoriesQuery extends BaseQuery
{
    public const NAME = 'categories';

    public function args(): array
    {
        return array_merge(
            $this->sortArgs(),
            [
                'title' => [
                    'type' => Type::string()
                ],
                'active' => [
                    'type' => Type::boolean()
                ],
                'parent_id' => [
                    'type' => Type::id()
                ],
                'ids' => [
                    'type' => Type::listOf(Type::id()),
                ],
                'with_olmo' => [
                    'type' => Type::boolean(),
                ],
                'with_spares' => [
                    'type' => Type::boolean(),
                ],
            ]
        );
    }

    protected function getQuery(SelectFields $fields, array $args): Builder|Category
    {
        $select = $fields->getSelect() ?: ['id'];

        return Category::withProducts($this->user())
            ->forGuard($this->user())
            ->select(array_merge($select, [Category::TABLE . '.parent_id']))
            ->when(
                !($args['parent_id'] ?? false),
                static function (Builder $b) use ($args) {
                    if (empty($args['ids'])) {
                        $b->whereNull('parent_id');
                    }
                }
            )
            ->when(
                !(isset($args['with_olmo']) && $args['with_olmo']),
                static fn($b) => $b->cooper()
            )
            ->when(
                !isset($args['with_spares']) || (isset($args['with_spares']) && $args['with_spares'] == false),
                static fn($b) => $b->where('slug', '!=', 'spares')
            )
            ->filter($args)
            ->with($fields->getRelations());
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            array_merge(
                $this->paginationRules(),
                [
                    'id' => ['nullable', 'integer'],
                    'title' => ['nullable', 'string'],
                    'parent_id' => ['nullable', 'int'],
                    'active' => ['nullable', 'boolean'],
                ]
            )
        );
    }
}

