<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\FrontOffice\News;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\News\VideoType;
use App\Models\BaseModel;
use App\Models\News\Video;
use App\Traits\GraphQL\HasNextPrevLinks;
use Core\GraphQL\Queries\GenericQuery;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class VideosQuery extends GenericQuery
{
    use HasNextPrevLinks;

    public const NAME = 'videos';

    protected BaseModel|string $model = Video::class;
    protected BaseType|string $type = VideoType::class;

    public function args(): array
    {
        return array_merge(
            parent::args(),
            $this->getSlugsArgs(),
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
