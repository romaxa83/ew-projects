<?php

namespace App\GraphQL\Queries\BackOffice\Musics;

use App\GraphQL\Types\Musics\MusicType;
use App\Repositories\Musics\MusicRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;

class MusicsListQuery extends BaseQuery
{
    public const NAME = 'MusicsList';
    public const PERMISSION = Permissions\Musics\ListPermission::KEY;

    public function __construct(protected MusicRepository $repo)
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool
    {
        $this->setAuthGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => Type::id(),
            'active' => Type::boolean(),
        ];
    }

    public function type(): Type
    {
        return MusicType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->getList(
            filters: $args
        );
    }
}


