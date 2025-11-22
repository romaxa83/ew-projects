<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Certificates;

use App\GraphQL\Types\Catalog\Certificates\CertificateType;
use App\Models\Catalog\Certificates\Certificate;
use App\Permissions\Catalog\Certificates\Certificate\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class CertificatesQuery extends BaseQuery
{
    public const NAME = 'certificates';
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
                'type' => Type::id(),
                'link' => Type::string(),
                'number' => Type::string(),
            ]
        );
    }

    public function type(): Type
    {
        return CertificateType::paginate();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            Certificate::query()
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
                'type' => ['nullable', 'integer'],
                'link' => ['nullable', 'string'],
                'number' => ['nullable', 'string'],
            ]
        );
    }
}





