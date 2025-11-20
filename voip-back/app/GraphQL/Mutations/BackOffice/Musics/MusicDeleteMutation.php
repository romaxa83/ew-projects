<?php

namespace App\GraphQL\Mutations\BackOffice\Musics;

use App\GraphQL\Types\NonNullType;
use App\Models\Departments\Department;
use App\Models\Musics\Music;
use App\Permissions;
use App\Repositories\Musics\MusicRepository;
use App\Services\Musics\MusicService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MusicDeleteMutation extends BaseMutation
{
    public const NAME = 'MusicDelete';
    public const PERMISSION = Permissions\Musics\DeletePermission::KEY;

    public function __construct(
        protected MusicService $service,
        protected MusicRepository $repo
    )
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null): bool
    {
        $this->setAdminGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Music::class, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        /** @var $model Music*/
        $model = $this->repo->getBy('id', $args['id'],
            withException: true,
            exceptionMessage: "Music not found by id [{$args['id']}]"
        );

        return $this->service->remove($model);
    }
}
