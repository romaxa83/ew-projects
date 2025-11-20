<?php

namespace App\IPTelephony\Services\Storage\Asterisk;

use App\IPTelephony\Entities\Asterisk\QueueEntity;
use App\Models\Departments\Department;
use App\Models\Musics\Music;
use PHPUnit\Util\Exception;

class QueueService extends AsteriskService
{
    public function getTable(): string
    {
        return QueueEntity::TABLE;
    }

    public function create(Department $model, array $customData = [])
    {
        if($res = $this->insert(
            $this->prepareDataForInsert($model, $customData)
        )){
            Department::query()->update(['is_insert_asterisk' => $res]);
        }
    }

    public function editOrCreate(Department $model)
    {
        $queue = $this->getBy('uuid', $model->guid);
        if ($queue) {
            $this->edit($model);
        } else {
            $this->create($model);
        }
    }

    public function editOnlyMusicData(Music $model)
    {
        $queue = $this->getBy('uuid', $model->department->guid);

        if(!$queue) throw new Exception("Not found queue by - [".$model->department->guid."]");

        logger_info('UPDATE MUSIC RECS ASTERISK', [
            'periodic_announce' => $model->unhold_data['periodic_announce']
                ?? config('asterisk.music.prefix_for_name'). pretty_file_name($model->media->first()->name ?? ''),
            'periodic_announce_frequency' => $model->unhold_data['periodic_announce_frequency'] ?? $model->interval,
            'relative_periodic_announce' => $model->unhold_data['relative_periodic_announce'] ?? 'yes'
        ]);

        $this->update($model->department->guid, [
            'periodic_announce' => $model->unhold_data['periodic_announce']
                ?? config('asterisk.music.prefix_for_name'). pretty_file_name($model->media->first()->name ?? ''),
            'periodic_announce_frequency' => $model->unhold_data['periodic_announce_frequency'] ?? $model->interval,
            'relative_periodic_announce' => $model->unhold_data['relative_periodic_announce'] ?? 'yes'
        ]);
    }

    public function deleteOnlyMusicData(Music $model)
    {
        $queue = $this->getBy('uuid', $model->department->guid);

        if(!$queue) throw new Exception("Not found queue by - [$model->department->guid]");

        $this->update($model->department->guid, [
            'periodic_announce' => null,
            'periodic_announce_frequency' => null,
            'relative_periodic_announce' => null
        ]);
    }

    public function edit(Department $model)
    {
        return $this->update($model->guid, $this->prepareDataForInsert($model));
    }

    public function remove(Department $model)
    {
        $queue = $this->getBy('uuid', $model->guid);
        if(!$queue){
            logger_info("NOT FOUND Queue TO UUID [{$model->guid}]");
            return true;
        }

        $model->update(['is_insert_asterisk' => false]);

        if($res = $this->deleteBy('name', $queue->name)){
            logger_info("DELETE queue [asterisk] SUCCESS [{$model->id}]");
        }

        return $res;
    }

    public function prepareDataForInsert(Department $model, array $customData = []): array
    {
        return [
            'name' => $model->name,
            'uuid' => $model->guid,
            'num' => $model->num,
            'musiconhold' => config('asterisk.queue.musiconhold'),
            'timeout' => config('asterisk.queue.timeout'),
            'queue_timeout' => config('asterisk.queue.queue_timeout'),
            'ringinuse' => config('asterisk.queue.ringinuse'),
            'setinterfacevar' => config('asterisk.queue.setinterfacevar'),
            'setqueuevar' => config('asterisk.queue.setqueuevar'),
            'setqueueentryvar' => config('asterisk.queue.setqueueentryvar'),
            'announce_frequency' => config('asterisk.queue.announce_frequency'),
            'announce_to_first_user' => config('asterisk.queue.announce_to_first_user'),
            'announce_position_limit' => config('asterisk.queue.announce_position_limit'),
            'announce_holdtime' => config('asterisk.queue.announce_holdtime'),
            'announce_position' => config('asterisk.queue.announce_position'),
            'periodic_announce' => data_get($customData, 'periodic_announce', config('asterisk.queue.periodic_announce')),
            'periodic_announce_frequency' => data_get($customData, 'periodic_announce_frequency', config('asterisk.queue.periodic_announce_frequency')),
            'relative_periodic_announce' => data_get($customData, 'relative_periodic_announce', config('asterisk.queue.relative_periodic_announce')),
            'retry' => config('asterisk.queue.retry'),
            'wrapuptime' => config('asterisk.queue.wrapuptime'),
            'autofill' => config('asterisk.queue.autofill'),
            'autopause' => config('asterisk.queue.autopause'),
            'autopausebusy' => config('asterisk.queue.autopausebusy'),
            'autopauseunavail' => config('asterisk.queue.autopauseunavail'),
            'maxlen' => config('asterisk.queue.maxlen'),
            'servicelevel' => config('asterisk.queue.servicelevel'),
            'strategy' => config('asterisk.queue.strategy'),
            'leavewhenempty' => config('asterisk.queue.leavewhenempty'),
            'reportholdtime' => config('asterisk.queue.reportholdtime'),
            'memberdelay' => config('asterisk.queue.memberdelay'),
            'weight' => config('asterisk.queue.weight'),
            'timeoutrestart' => config('asterisk.queue.timeoutrestart'),
            'timeoutpriority' => config('asterisk.queue.timeoutpriority'),
            'language_default' => config('asterisk.queue.language_default'),
        ];
    }
}
