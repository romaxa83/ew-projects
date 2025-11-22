<?php

namespace App\GraphQL\Queries\BackOffice\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\Commercial\CredentialsRequestType;
use App\GraphQL\Types\Enums\Commercial\CommercialCredentialsStatusEnumType;
use App\Models\Commercial\CredentialsRequest;
use App\Permissions\Commercial\Credentials\CredentialsListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class CredentialsRequestsQuery extends BaseQuery
{
    public const NAME = 'credentialsRequests';
    public const PERMISSION = CredentialsListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'id' => [
                    'type' => Type::id(),
                ],
                'status' => [
                    'type' => CommercialCredentialsStatusEnumType::type(),
                ],
                'per_page' => [
                    'type' => Type::int(),
                    'defaultValue' => config('queries.default.pagination.per_page')
                ],
                'page' => [
                    'type' => Type::int(),
                    'defaultValue' => 1
                ],
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
            ],
            $this->sortArgs('created_at-desc'),
        );
    }

    public function type(): Type
    {
        return CredentialsRequestType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {

        return $this->paginate(
            CredentialsRequest::query()
//                ->select($fields->getSelect() ?: ['id'])
                ->with($fields->getRelations())
                ->filter($args),
            $args
        );
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            [],
            $this->sortRules()
        );
    }

    protected function allowedForSortFields(): array
    {
        return CredentialsRequest::ALLOWED_SORTING_FIELDS;
    }
}
