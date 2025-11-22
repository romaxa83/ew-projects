<?php

namespace App\GraphQL\Queries\BackOffice\Commercial\Commissioning;

use App\GraphQL\Types\Commercial\Commissioning\ProtocolType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\ProtocolTypeEnumType;
use App\Permissions\Commercial\Commissionings\Protocol\ListPermission;
use App\Repositories\Commercial\Commissioning\ProtocolRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class ProtocolQuery extends BaseQuery
{
    public const NAME = 'commissioningProtocols';
    public const PERMISSION = ListPermission::KEY;

    public function __construct(protected ProtocolRepository $repo)
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
                'type' => [
                    'type' => ProtocolTypeEnumType::type(),
                    'description' => 'Filter by type',
                ],
            ]
        );
    }

    public function type(): Type
    {
        return ProtocolType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return $this->repo->getAllPagination(
            $fields->getRelations(),
            $args,
            'sort'
//            ['type' => 'asc']
        );
    }
}

