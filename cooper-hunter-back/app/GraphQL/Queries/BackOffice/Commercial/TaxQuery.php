<?php

namespace App\GraphQL\Queries\BackOffice\Commercial;

use App\GraphQL\Types\Commercial\TaxType;
use App\Models\Commercial\Tax;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class TaxQuery extends BaseQuery
{
    public const NAME = 'taxes';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return TaxType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return Tax::query()->get();
    }
}

