<?php

namespace App\GraphQL\Mutations\BackOffice\Musics;

use App\Dto\Music\MusicDto;
use App\GraphQL\InputTypes\Musics\MusicInput;
use App\GraphQL\Types\Musics\MusicType;
use App\Models\Departments\Department;
use App\Models\Musics\Music;
use App\Models\Schedules\Schedule;
use App\Permissions;
use App\Services\Musics\MusicService;
use Carbon\CarbonImmutable;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;
class MusicCreateMutation extends BaseMutation
{
    public const NAME = 'MusicCreate';
    public const PERMISSION = Permissions\Musics\CreatePermission::KEY;

    public function __construct(
        protected MusicService $service
    )
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null)
    : bool
    {
        $this->setAdminGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
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


        $anotherMusic = Music::first();
        if($anotherMusic && $anotherMusic->isHoldState()){
            throw new TranslatedException(__('exceptions.music.hold'));
        } else {
            // todo данный код дублируется в воркере hold music, вынести в отдельный метод
            $now = dateByTz(CarbonImmutable::now());
            $weekDay = strtolower($now->isoFormat('dddd'));
            $schedule = Schedule::first();
            $scheduleDay = $schedule->days()->where('name', $weekDay)->first();

            if($scheduleDay && $scheduleDay->end_work_time){
                $endWorkTime = CarbonImmutable::createFromTimeString($scheduleDay->end_work_time);

                logger_info('CREATE MUSIC', [
                    'NOW' => $now,
                    'END WORK TIME' => $endWorkTime,
                    'time_point' => $now->addMinutes(config('asterisk.music.hold_to_end_work_day') ),
                    'equals' => $now->addMinutes(config('asterisk.music.hold_to_end_work_day')) >= $endWorkTime
                        && $now < $endWorkTime
                ]);

                if(
                    $now->addMinutes(config('asterisk.music.hold_to_end_work_day')) >= $endWorkTime
                    && $now < $endWorkTime
                ){
                    throw new TranslatedException(__('exceptions.music.hold'));
                }
            }
        }


        $model = $this->service->create(MusicDto::byArgs($args['input']));

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
                Rule::unique(Music::class, 'department_id'),
            ],
        ];
    }
}

