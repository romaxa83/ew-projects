<?php

namespace App\GraphQL\Queries\Common\Localization;

use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Translate;
use App\Traits\Localization\LocalizationCacheTags;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use JsonException;
use Rebing\GraphQL\Support\SelectFields;

class TranslatesSimpleHashQuery extends BaseQuery
{
    use LocalizationCacheTags;

    public const NAME = 'translatesSimpleHash';
    public const DESCRIPTION = 'Translations hash';

    public function type(): Type
    {
        return NonNullType::string();
    }

    public function args(): array
    {
        return [
            'place' => NonNullType::listOfString(),
            'lang' => NonNullType::listOfString(),
        ];
    }

    /**
     * @throws JsonException
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): string
    {
        $translations = Translate::query()
            ->select(['id', 'created_at', 'updated_at', 'place', 'key', 'text', 'lang'])
            ->cacheFor(60)
            ->cacheTags($this->getCacheTags($args))
            ->filter($args)
            ->latest()
            ->getQuery()
            ->get()
            ->toArray();

        $translations = arrayToJson($translations);

        return md5($translations);
    }

    protected function rules(array $args = []): array
    {
        return [
            'place' => ['required', 'array'],
            'lang' => ['required', 'array'],
        ];
    }
}