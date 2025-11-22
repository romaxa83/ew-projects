<?php

namespace App\GraphQL\Queries\BackOffice\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\Commercial\CommercialQuoteType;
use App\GraphQL\Types\Enums\Commercial\CommercialQuoteStatusEnumType;
use App\Models\Commercial\CommercialQuote;
use App\Permissions\Commercial\CommercialQuotes\CommercialQuoteListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class CommercialQuotesQuery extends BaseQuery
{
    public const NAME = 'commercialQuotes';
    public const PERMISSION = CommercialQuoteListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        $parent = parent::args();
        unset(
            $parent['created_at'],
            $parent['updated_at'],
        );

        return array_merge(
            $parent,
            [
                'status' => [
                    'type' => CommercialQuoteStatusEnumType::type(),
                    'description' => 'Filter by status',
                ],
                'project_name' => [
                    'type' => Type::string(),
                    'description' => 'Filter by project name',
                ],
                'technician_name' => [
                    'type' => Type::string(),
                    'description' => 'Filter by technician name',
                ],
                'date_from' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string', DatetimeEnum::DATE_RULE],
                    'description' => 'Filter by field "created_at" FROM given date, format - "Y-m-d"',
                ],
                'date_to' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string', DatetimeEnum::DATE_RULE],
                    'description' => 'Filter by field "created_at" TO given date, format - "Y-m-d"',
                ],
            ]
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
    ): LengthAwarePaginator {

        return $this->paginate(
            CommercialQuote::query()
//                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations())
                ->with(['items.product'])
                ->latest(),
            $args,
        );
    }
}
