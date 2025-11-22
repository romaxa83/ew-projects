<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Videos\Links;

use App\GraphQL\Types\Catalog\Videos\LInks;
use App\Models\Catalog\Videos\VideoLink;
use App\Permissions\Catalog\Videos\Link\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class VideoLinksQuery extends BaseQuery
{
    public const NAME = 'videoLinks';
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
                'link' => Type::string(),
                'active' => Type::boolean(),
                'group_id' => Type::id(),
                'title' => Type::string(),
            ]
        );
    }

    public function type(): Type
    {
        return Links\VideoLinkType::paginate();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {

        return $this->paginate(
            VideoLink::query()
                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations())
                ->latest('sort'),
            $args
        );
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            $this->paginationRules(),
            [
                'id' => ['nullable', 'integer'],
                'link' => ['nullable', 'string'],
                'active' => ['nullable', 'boolean'],
                'group_id' => ['nullable', 'integer'],
                'title' => ['nullable', 'string'],
            ]
        );
    }
}





