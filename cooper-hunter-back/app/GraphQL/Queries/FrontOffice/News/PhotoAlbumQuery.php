<?php

namespace App\GraphQL\Queries\FrontOffice\News;

use App\GraphQL\Types\Media\MediaType;
use App\Models\News\PhotoAlbum;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class PhotoAlbumQuery extends BaseQuery
{
    public const NAME = 'photoAlbum';

    public function type(): Type
    {
        return MediaType::paginate();
    }

    public function args(): array
    {
        return $this->paginationArgs();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        $album = PhotoAlbum::query()->firstOrCreate();

        return $this->paginate(
            $album->media()->orderByDesc('created_at'),
            $args
        );
    }
}
