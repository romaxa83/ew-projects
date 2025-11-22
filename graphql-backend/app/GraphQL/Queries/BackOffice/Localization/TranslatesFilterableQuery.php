<?php

namespace App\GraphQL\Queries\BackOffice\Localization;

use App\Filters\Localization\TranslateAdminFilter;
use App\GraphQL\Types\Localization\TranslateType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Translate;
use App\Permissions\Localization\TranslateListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class TranslatesFilterableQuery extends BaseQuery
{
    public const NAME = 'translationsFilterable';
    public const PERMISSION = TranslateListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return $this->paginateType(
            TranslateType::type()
        );
    }

    public function args(): array
    {
        return [
            'place' => NonNullType::listOfString(),
            'key' => Type::string(),
            'text' => Type::string(),
            'lang' => Type::listOf(
                Type::string()
            ),
            'page' => Type::int(),
            'limit' => Type::int(),
            'sort' => [
                'type' => Type::string(),
                'description' => 'Параметры сортировки. Могут быть выбраны любые поля данного типа + направление кроме place.
                Например: place-desc, text-asc',
                'rules' => ['sometimes']
            ],
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): LengthAwarePaginator
    {
        return Translate::query()
            ->select($fields->getSelect())
            ->filter($args, TranslateAdminFilter::class)
            ->getQuery()
            ->paginate(
                $args['limit'] ?? config('queries.localization.translates_filterable.limit'),
                ['*'],
                'page',
                $args['page'] ?? 1
            );
    }

    protected function rules(array $args = []): array
    {
        return [
            'place' => ['required', 'array'],
            'lang' => ['nullable', 'array'],
        ];
    }
}
