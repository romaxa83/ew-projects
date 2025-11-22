<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\FrontOffice\News;

use App\GraphQL\Types\News\TagType;
use App\Models\News\Tag;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class TagsQuery extends BaseQuery
{
    public const NAME = 'tags';

    public function type(): Type
    {
        return TagType::list();
    }

    public function args(): array
    {
        return [];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return Tag::query()
            ->with('translation')
            ->get();
    }
}
