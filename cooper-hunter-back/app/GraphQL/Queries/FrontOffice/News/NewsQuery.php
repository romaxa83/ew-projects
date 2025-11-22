<?php

namespace App\GraphQL\Queries\FrontOffice\News;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\News\NewsType;
use App\Models\BaseModel;
use App\Models\News\News;
use App\Traits\GraphQL\HasNextPrevLinks;
use Core\GraphQL\Queries\GenericQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class NewsQuery extends GenericQuery
{
    use HasNextPrevLinks;

    public const NAME = 'news';

    protected BaseModel|string $model = News::class;
    protected BaseType|string $type = NewsType::class;

    public function args(): array
    {
        return array_merge(
            parent::args(),
            $this->getSlugsArgs(),
            [
                'tag_id' => [
                    'type' => Type::id(),
                ],
            ],
        );
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        $paginator = $this->paginate(
            $this->model::query()
                ->select($fields->getSelect())
                ->with($fields->getRelations())
                ->latest('created_at')
                ->filter($args),
            $args,
        );

        $this->setNextPrevLinks($paginator, $args);

        return $paginator;
    }

    protected function initArgs(array $args): array
    {
        $args['active'] = true;

        return $args;
    }
}
