<?php

namespace App\GraphQL\Mutations\BackOffice\Musics;

use App\GraphQL\Types\Musics\MusicType;
use App\IPTelephony\Events\Queue\QueueUpdateMusicEvent;
use App\Models\Musics\Music;
use App\Permissions;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\Services\Musics\MusicService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Closure;

class MusicUploadRecordMutation extends BaseMutation
{
    public const NAME = 'MusicUploadRecord';
    public const PERMISSION = Permissions\Musics\UploadPermission::KEY;

    public function __construct(
        protected MusicService $service,
    )
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool
    {
        $this->setAdminGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function type(): Type
    {
        return MusicType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Music::class, 'id')],
            ],
            'media' => [
                'type' => FileType::nonNullType(),
//                'rules' => ['required', 'file', 'mimetypes:audio/mpeg,audio/x-mpeg'],
            ]
        ];
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Music
    {
        /** @var $model Music */
        $model = $this->service->repo->getBy('id', $args['id']);

        if($model->isHoldState()){
            throw new TranslatedException(__('exceptions.music.hold'));
        }

        /** @var $model Music */
        $model = $this->service->upload(
            $model,
            $args['media']
        );

        if($model->isActive()) event(new QueueUpdateMusicEvent($model));

        return $model;
    }
}
