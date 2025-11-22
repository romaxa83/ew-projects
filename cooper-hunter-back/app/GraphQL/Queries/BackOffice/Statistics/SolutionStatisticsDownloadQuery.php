<?php

namespace App\GraphQL\Queries\BackOffice\Statistics;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\NonNullType;
use App\Permissions\Catalog\Solutions\SolutionReadPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Crypt;
use Rebing\GraphQL\Support\SelectFields;

class SolutionStatisticsDownloadQuery extends BaseQuery
{
    public const NAME = 'solutionStatisticsDownload';
    public const PERMISSION = SolutionReadPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return NonNullType::string();
    }

    public function args(): array
    {
        return [
            'date_from' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', DatetimeEnum::DATE_RULE],
                'description' => 'Filter by field "end_date" FROM given date',
            ],
            'date_to' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', DatetimeEnum::DATE_RULE],
                'description' => 'Filter by field "end_date" TO given date',
            ],
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): string
    {
        $token = Crypt::encryptString(now()->addMinute()->getTimestamp());
        $filters = http_build_query($args);

        return route('statistics.solutions', compact('token', 'filters'));
    }
}