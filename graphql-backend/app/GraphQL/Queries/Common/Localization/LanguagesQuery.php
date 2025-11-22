<?php

namespace App\GraphQL\Queries\Common\Localization;

use App\GraphQL\Types\Localization\LanguageType;
use App\Models\Localization\Language;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class LanguagesQuery extends BaseQuery
{
    public const NAME = 'languages';

    public function type(): Type
    {
        return Type::listOf(
            LanguageType::type()
        );
    }

    public function args(): array
    {
        return [];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): array
    {
        return Language::query()
            ->select($fields->getSelect())
            ->cacheFor(config('queries.localization.languages.cache'))
            ->getQuery()
            ->get()
            ->toArray();
    }
}
