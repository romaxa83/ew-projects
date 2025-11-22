<?php

namespace App\GraphQL\Queries\BackOffice\Commercial;

use App\GraphQL\Types\Commercial\CommercialQuoteHistoryType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\QuoteHistory;
use App\Permissions\Commercial\CommercialQuotes\CommercialQuoteListPermission;
use App\Repositories\Commercial\CommercialQuoteHistoryRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class CommercialQuoteHistoriesQuery extends BaseQuery
{
    public const NAME = 'commercialQuoteHistories';
    public const PERMISSION = CommercialQuoteListPermission::KEY;

    public function __construct(protected CommercialQuoteHistoryRepository $repo)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'quote_id' => [
                'name' => 'quote_id',
                'type' => NonNullType::id(),
                'description' => 'Quotes id'
            ],
            'id' => [
                'name' => 'id',
                'type' => Type::id(),
                'description' => 'History id'
            ],
            'per_page' => [
                'type' => Type::int(),
                'defaultValue' => config('queries.default.pagination.per_page')
            ],
            'page' => [
                'type' => Type::int(),
                'defaultValue' => 1
            ],
        ];
    }

    public function type(): Type
    {
        return CommercialQuoteHistoryType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {

        return $this->paginate(
            QuoteHistory::query()
//                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations())
                ->where('quote_id', $args['quote_id'])
                ->latest(),
            $args,
        );
    }
}

