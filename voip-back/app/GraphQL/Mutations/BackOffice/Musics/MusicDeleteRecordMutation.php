<?php

namespace App\GraphQL\Mutations\BackOffice\Musics;

use App\GraphQL\Types\Musics\MusicType;
use App\GraphQL\Types\NonNullType;
use App\IPTelephony\Events\Queue\QueueDeleteMusicEvent;
use App\Models\Media\Media;
use App\Models\Musics\Music;
use App\Permissions;
use App\Repositories\Musics\MusicRepository;
use App\Services\Musics\MusicService;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MusicDeleteRecordMutation extends BaseMutation
{
    public const NAME = 'MusicRecordDelete';
    public const PERMISSION = Permissions\Musics\UploadPermission::KEY;

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
            'media_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Media::class, 'id')],
                'description' => 'Media ID'
            ],
        ];
    }

    public function type(): Type
    {
        return MusicType::nonNullType();
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
    ): Music
    {
        $media = Media::query()->where('id', $args['media_id'])->firstOrFail();

        if($media->model->isHoldState()){
            throw new TranslatedException(__('exceptions.music.hold'));
        }

        $this->service->removeRecord($media->model);

        return $media->model;
    }
}
