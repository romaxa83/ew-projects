<?php

namespace App\GraphQL\Queries\FrontOffice\Utilities;

use App\Models\Locations\IpRange;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\DB;
use Rebing\GraphQL\Support\SelectFields;

class ZipCodeByIpAddressQuery extends BaseQuery
{
    public const NAME = 'zipCodeByIpAddress';

    public function args(): array
    {
        return [
            'ip' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', 'ipv4']
            ],
        ];
    }

    public function type(): Type
    {
        return Type::string();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?string
    {
        $argIp = $args['ip'] ?? null;
        $requestIp = request()?->ip();

        if (!$userIp = $argIp ?: $requestIp) {
            return null;
        }

        $ip = ip2long($userIp);

        $range = IpRange::query()
            ->whereBetweenColumns(DB::raw($ip), ['ip_from', 'ip_to'])
            ->first();

        return $range?->zip;
    }
}