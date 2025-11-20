<?php

namespace App\Services\Musics;

use App\Dto\Music\MusicDto;
use App\IPTelephony\Events\Queue\QueueDeleteMusicEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use App\Models\Musics\Music;
use App\Models\Schedules\Schedule;
use App\Repositories\Musics\MusicRepository;
use App\Services\AbstractService;
use App\Services\FTP\FTPClient;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;

class MusicService extends AbstractService
{
    public function __construct(protected FTPClient $ftpClient)
    {
        parent::__construct();
        $this->repo = resolve(MusicRepository::class);
    }

    public function create(MusicDto $dto): Music
    {
        $model = new Music();

        $model = $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    public function update(Music $model, MusicDto $dto): Music
    {
        $model = $this->fill($model, $dto);

        $model->save();

        return $model;
    }
    private function fill(Music $model, MusicDto $dto): Music
    {
        $model->interval = $dto->interval;
        $model->active = $dto->active;
        $model->department_id = $dto->departmentId;

        return $model;
    }

    public function upload(Music $model, UploadedFile $file): Music
    {
        try {
            if($model->hasRecord()){
                $this->removeMedia($model);
            }

            $model
                ->copyMedia($file)
                ->setFileName(pretty_file_name($file->getClientOriginalName()))
                ->toMediaCollection(Music::MEDIA_COLLECTION_NAME);

            $this->ftpClient->upload($file);

            return $model->refresh();
        } catch (\Throwable $e){
            logger_info($e->getMessage());
        }
    }

    public function removeRecord(Music $model): Music
    {
        $this->removeMedia($model);

        event(new QueueDeleteMusicEvent($model));

        return $model;
    }

    public function removeMedia(Music $model): Music
    {
        if($this->ftpClient->exist($model->media->first()->file_name)) {
            $this->ftpClient->delete($model->media->first()->file_name);
        }

        $model->clearMediaCollection(Music::MEDIA_COLLECTION_NAME);

        return $model;
    }

    public function remove(Music $model): bool
    {
        if($model->hasRecord()){
            $this->removeRecord($model);
        }

        return $model->delete();
    }

    public function holdMusicToEndWorkDay(): bool
    {
        $now = dateByTz(CarbonImmutable::now());

        $weekDay = strtolower($now->isoFormat('dddd'));

        $schedule = Schedule::first();

        $scheduleDay = $schedule->days()->where('name', $weekDay)->first();

        if($scheduleDay && $scheduleDay->end_work_time){
            $endWorkTime = CarbonImmutable::createFromTimeString($scheduleDay->end_work_time);

            logger_info('HOLD MUSIC', [
                'day' => $scheduleDay,
                'endWorkTime' => $endWorkTime,
                'now' => $now,
                'time_point' => $now->addMinutes(config('asterisk.music.hold_to_end_work_day')),
                'equals' => $now->addMinutes(config('asterisk.music.hold_to_end_work_day')) >= $endWorkTime
                    && $now < $endWorkTime
            ]);

            if(
                $now->addMinutes(config('asterisk.music.hold_to_end_work_day')) >= $endWorkTime
                && $now < $endWorkTime
            ){
                $queueService = resolve(QueueService::class);

                $musics = Music::query()
                    ->with(['department'])
                    ->where('has_unhold_data', false)
                    ->get();

                logger_info('HOLD MUSIC', [
                    'count' => $musics->count()
                ]);

                foreach ($musics as $music){
                    /** @var $music Music */

                    $queue = $queueService->getBy('uuid', $music->department->guid);
                    $data = [
                        'periodic_announce' => $queue->periodic_announce,
                        'periodic_announce_frequency' => $queue->periodic_announce_frequency,
                        'relative_periodic_announce' => $queue->relative_periodic_announce,
                    ];

                    $music->update([
                        'unhold_data' => $data,
                        'has_unhold_data' => true
                    ]);
                    $queueService->deleteOnlyMusicData($music);
                }

                if($musics->isNotEmpty()){
                    return true;
                }
            }
        }

        return false;
    }

    public function unholdMusicToEndWorkDay()
    {
        $now = dateByTz(CarbonImmutable::now());

        $weekDay = strtolower($now->isoFormat('dddd'));

        $schedule = Schedule::first();

        $scheduleDay = $schedule->days()->where('name', $weekDay)->first();
        if($scheduleDay && $scheduleDay->end_work_time){
            $endWorkTime = CarbonImmutable::createFromTimeString($scheduleDay->end_work_time);

            logger_info('UNHOLD MUSIC', [
                'day' => $scheduleDay,
                'endWorkTime' => $endWorkTime,
                'now' => $now,
                'equals' => $now >= $endWorkTime
            ]);

            if($now >= $endWorkTime->addMinutes(5)){
                self::unholdMusic();
            }
        }
    }

    public static function unholdMusic(): void
    {
        $queueService = resolve(QueueService::class);

        $musics = Music::query()
            ->with(['department'])
            ->where('has_unhold_data', true)
            ->get();

        logger_info('UNHOLD MUSIC', [
            'count' => $musics->count()
        ]);

        foreach ($musics as $music){
            /** @var $music Music */

            $queueService->editOnlyMusicData($music);

            $music->update([
                'unhold_data' => null,
                'has_unhold_data' => false
            ]);
        }
    }

    public static function holdMusic(): void
    {
        $queueService = resolve(QueueService::class);

        $musics = Music::query()
            ->with(['department'])
            ->where('has_unhold_data', true)
            ->get();

        logger_info('UNHOLD MUSIC', [
            'count' => $musics->count()
        ]);

        foreach ($musics as $music){
            /** @var $music Music */

            $queueService->editOnlyMusicData($music);

            $music->update([
                'unhold_data' => null,
                'has_unhold_data' => false
            ]);
        }
    }
}

