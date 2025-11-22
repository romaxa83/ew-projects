<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Certificates;

use App\GraphQL\Types\Catalog\Certificates\CertificateTypeType;
use App\Models\Catalog\Certificates\CertificateType;
use App\Permissions\Catalog\Certificates\Type\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class CertificateTypesQuery extends BaseQuery
{
    public const NAME = 'certificateTypes';
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            $this->sortArgs(),
            [
                'id' => Type::id(),
                'type' => Type::string(),
            ]
        );
    }

    public function type(): Type
    {
        return CertificateTypeType::paginate();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            CertificateType::query()
                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations())
                ->latest('id'),
            $args
        );
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            $this->paginationRules(),
            [
                'id' => ['nullable', 'integer'],
                'type' => ['nullable', 'string'],
            ]
        );
    }
}





