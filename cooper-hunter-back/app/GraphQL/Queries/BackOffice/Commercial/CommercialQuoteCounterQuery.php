<?php

namespace App\GraphQL\Queries\BackOffice\Commercial;

use App\GraphQL\Types\Commercial\CommercialQuoteCounterType;
use App\Permissions\Commercial\CommercialQuotes\CommercialQuoteListPermission;
use App\Repositories\Commercial\CommercialQuoteRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class CommercialQuoteCounterQuery extends BaseQuery
{
    public const NAME = 'commercialQuoteCounter';
    public const PERMISSION = CommercialQuoteListPermission::KEY;

    public function __construct(protected CommercialQuoteRepository $repo)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return CommercialQuoteCounterType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->repo->getCounterData();
    }
}
