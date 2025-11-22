<?php

namespace App\GraphQL\Queries\Common\About;

use App\Enums\About\ForMemberPageEnum;
use App\GraphQL\Types\About\ForMemberPageType;
use App\GraphQL\Types\Enums\About\ForMemberPageEnumType;
use App\Models\About\ForMemberPage;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseForMemberPageQuery extends BaseQuery
{
    public const NAME = 'forMemberPage';

    public function args(): array
    {
        return [
            'for_member_type' => [
                'type' => ForMemberPageEnumType::nonNullType(),
            ]
        ];
    }

    public function type(): Type
    {
        return ForMemberPageType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?ForMemberPage {
        return ForMemberPage::query()
            ->forMemberType(
                ForMemberPageEnum::fromValue($args['for_member_type'])
            )
            ->first();
    }
}
