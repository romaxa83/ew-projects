<?php

namespace App\GraphQL\Queries\Common\Localization;

use App\GraphQL\Types\Enums\Localization\LanguageEnumType;
use App\GraphQL\Types\Localization\TranslateType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Translate;
use App\Services\Localizations\TranslateService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseTranslatesListQuery extends BaseQuery
{
    public const NAME = 'translatesList';
    //public const PERMISSION = TranslateShowPermission::KEY;

    public function __construct(private TranslateService $service)
    {
    }

    abstract protected function setQueryGuard(): void;

    public function type(): Type
    {
        return TranslateType::list();
    }

    public function args(): array
    {
        $args = array_merge(
            $this->buildArgs(
                Translate::AVAILABLE_SORT_FIELDS,
                [
                    'key',
                    'place',
                    'text'
                ]
            ),
            [
                'place' => [
                    'type' => NonNullType::listOfString()
                ],
                'key' => [
                    'type' => Type::string()
                ],
                'lang' => [
                    'type' => LanguageEnumType::nonNullList()
                ],
            ]
        );

        unset($args['per_page'], $args['page']);

        return $args;
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Collection
    {
        return $this->service->getList($args);
    }
}
