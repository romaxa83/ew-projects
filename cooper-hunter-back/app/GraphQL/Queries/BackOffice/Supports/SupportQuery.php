<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\BackOffice\Supports;

use App\GraphQL\Types\Supports\SupportType;
use App\Models\Support\Supports\Support;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class SupportQuery extends BaseQuery
{
    public const NAME = 'support';

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
        return SupportType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?Support {
        return Support::query()
            ->select($fields->getSelect() ?: ['phone'])
            ->with($fields->getRelations())
            ->first();
    }
}
