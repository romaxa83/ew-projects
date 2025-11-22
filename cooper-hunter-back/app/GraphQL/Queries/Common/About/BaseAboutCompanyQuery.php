<?php

namespace App\GraphQL\Queries\Common\About;

use App\GraphQL\Types\About\AboutCompanyType;
use App\Models\About\AboutCompany;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseAboutCompanyQuery extends BaseQuery
{
    public const NAME = 'aboutCompany';

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return AboutCompanyType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?AboutCompany {
        return AboutCompany::query()->first();
    }
}
