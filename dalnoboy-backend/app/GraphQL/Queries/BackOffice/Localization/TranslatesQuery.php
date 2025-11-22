<?php

namespace App\GraphQL\Queries\BackOffice\Localization;

use App\GraphQL\Types\Enums\Localization\LanguageEnumType;
use App\GraphQL\Types\Localization\TranslateType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Translate;
use App\Permissions\Localization\TranslateShowPermission;
use App\Services\Localizations\TranslateService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class TranslatesQuery extends BaseQuery
{
    public const NAME = 'translates';
    public const PERMISSION = TranslateShowPermission::KEY;

    public function __construct(private TranslateService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TranslateType::paginate();
    }

    public function args(): array
    {
        return array_merge(
            $this->buildArgs(
                Translate::AVAILABLE_SORT_FIELDS,
                [
                    'place',
                    'key',
                    'text',
                ]
            ),
            [
                'lang' => [
                    'type' => LanguageEnumType::list()
                ],
                'place' => [
                    'type' => Type::listOf(
                        NonNullType::string()
                    )
                ]
            ]
        );
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): LengthAwarePaginator
    {
        return $this->service->show(
            $args,
            $fields->getSelect()
        );
    }
}
