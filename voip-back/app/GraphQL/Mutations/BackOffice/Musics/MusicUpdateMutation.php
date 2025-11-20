<?php

namespace App\GraphQL\Mutations\BackOffice\Musics;

use App\Dto\Music\MusicDto;
use App\GraphQL\InputTypes\Musics\MusicInput;
use App\GraphQL\Types\Musics\MusicType;
use App\GraphQL\Types\NonNullType;
use App\IPTelephony\Events\Queue\QueueDeleteMusicEvent;
use App\IPTelephony\Events\Queue\QueueUpdateMusicEvent;
use App\Models\Departments\Department;
use App\Models\Musics\Music;
use App\Permissions;
use App\Services\Musics\MusicService;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MusicUpdateMutation extends BaseMutation
{
    public const NAME = 'MusicUpdate';
    public const PERMISSION = Permissions\Musics\UpdatePermission::KEY;

    public function __construct(
        protected MusicService $service
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
            'input' => MusicInput::nonNullType(),
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
        /** @var $model Music */
        $model = $this->service->repo->getBy('id', $args['id']);

        if($model->isHoldState()){
            throw new TranslatedException(__('exceptions.music.hold'));
        }

        $model = $this->service->update(
            $model,
            MusicDto::byArgs($args['input'])
        );

        if($model->hasRecord()){
            $model->isActive()
                ? event(new QueueUpdateMusicEvent($model))
                : event(new QueueDeleteMusicEvent($model))
            ;
        }

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.interval' => ['required', 'int', 'min:1'],
            'input.active' => ['required'],
            'input.department_id' => [
                'bail',
                'required',
                Rule::exists(Department::class, 'id'),
                Rule::unique(Music::class, 'department_id')->ignore($args['id']),
            ],
        ];
    }
}

