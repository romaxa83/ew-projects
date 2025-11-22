<?php

namespace App\GraphQL\Queries\Common\Localization;

use App\GraphQL\Types\Localization\TranslateType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Translate;
use App\Traits\Localization\LocalizationCacheTags;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class TranslatesSimpleQuery extends BaseQuery
{
    use LocalizationCacheTags;

    public const NAME = 'translatesSimple';

    public function type(): Type
    {
        return Type::listOf(
            TranslateType::type()
        );
    }

    public function args(): array
    {
        return [
            'place' => NonNullType::listOfString(),
            'lang' => NonNullType::listOfString(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): array
    {
        return Translate::query()
            ->select($fields->getSelect())
//            ->cacheFor(config('queries.localization.translations.cache'))
//            ->cacheTags($this->getCacheTags($args))
            ->filter($args)
            ->latest()
            ->getQuery()
            ->get()
            ->toArray();
    }

    protected function rules(array $args = []): array
    {
        return [
            'place' => ['required', 'array'],
            'lang' => ['required', 'array'],
        ];
    }
}
