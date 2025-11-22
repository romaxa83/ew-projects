<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Pdf;

use App\GraphQL\Types\Catalog\Pdf\PdfType;
use App\Models\Catalog\Pdf\Pdf;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class PdfsQuery extends BaseQuery
{
    public const NAME = 'pdfs';

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return PdfType::paginate();
    }

    public function args(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::id()
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

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {

        return Pdf::query()
                ->filter($args)
                ->latest('id')
                ->paginate(perPage: $args['per_page'], page: $args['page']);
    }
}

