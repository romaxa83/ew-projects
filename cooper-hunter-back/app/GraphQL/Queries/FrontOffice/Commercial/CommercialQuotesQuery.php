<?php

namespace App\GraphQL\Queries\FrontOffice\Commercial;

use App\GraphQL\Types\Commercial\CommercialQuoteType;
use App\GraphQL\Types\Enums\Commercial\CommercialQuoteStatusEnumType;
use App\Models\Commercial\CommercialQuote;
use App\Permissions\Commercial\CommercialQuotes\CommercialQuoteListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class CommercialQuotesQuery extends BaseQuery
{
    public const NAME = 'commercialQuotes';
    public const PERMISSION = CommercialQuoteListPermission::KEY;

    public function __construct()
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'status' => [
                    'type' => CommercialQuoteStatusEnumType::type(),
                    'description' => 'Filter by status',
                ],
            ],
        );
    }

    public function type(): Type
    {
        return CommercialQuoteType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        $this->isTechnicianCommercial();

        return CommercialQuote::query()
            ->filter($args)
            ->whereHas('commercialProject', fn ($q) => $q->where('member_id', $this->user()->id))
            ->latest('id')
            ->paginate(perPage: $args['per_page'], page: $args['page'])
            ;
    }
}
