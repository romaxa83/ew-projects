<?php

namespace App\GraphQL\Queries\BackOffice\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\Commercial\RDPAccountType;
use App\Models\Commercial\RDPAccount;
use App\Permissions\Commercial\Credentials\CredentialsListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class RDPAccountsQuery extends BaseQuery
{
    public const NAME = 'rdpAccounts';
    public const PERMISSION = CredentialsListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            $this->getIdArgs(),
            $this->getActiveArgs(),
            $this->sortArgs('end_date-desc'),
            [
                'end_date_from' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string', DatetimeEnum::DATE_RULE],
                    'description' => 'Filter by field "end_date" FROM given date',
                ],
                'end_date_to' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string', DatetimeEnum::DATE_RULE],
                    'description' => 'Filter by field "end_date" TO given date',
                ],
            ]
        );
    }

    public function type(): Type
    {
        return RDPAccountType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return $this->paginate(
            RDPAccount::query()
                ->select($fields->getSelect() ?: ['id'])
                ->with($fields->getRelations())
                ->filter($args),
            $args
        );
    }

    protected function allowedForSortFields(): array
    {
        return RDPAccount::ALLOWED_SORTING_FIELDS;
    }
}
