<?php

namespace App\IPTelephony\Services\Storage\Asterisk;

use App\Enums\Calls\QueueStatus;
use App\Enums\Calls\QueueType;
use App\IPTelephony\Entities\Asterisk\QueueMemberEntity;
use App\Models\Calls\Queue;
use App\Models\Employees\Employee;
use App\PAMI\Client\Impl\ClientAMI;
use App\PAMI\Message\Action\QueuePauseAction;
use App\PAMI\Message\Action\QueueUnpauseAction;
use Illuminate\Support\Facades\DB;

class QueueMemberService extends AsteriskService
{
    public function getTable(): string
    {
        return QueueMemberEntity::TABLE;
    }

    public function create(Employee $model)
    {
        if($res = $this->insert(
            $this->prepareDataForInsert($model)
        )){
            Employee::query()->update(['is_insert_queue' => $res]);

            logger_info("Create queue member [{$model->guid}]");
        }
    }

    public function editOrCreate(Employee $model)
    {
        $queue = $this->getBy('uuid', $model->guid);
        if ($queue) {
            $this->edit($model);
        } else {
            $this->create($model);
        }
    }

    public function updateQueueNames(string $oldName, string $newName): void
    {
        $items = $this->getAllBy('queue_name', $oldName);
        foreach ($items as $item){
            $this->update($item->uuid, ['queue_name' => $newName]);
        }
    }

    public function togglePaused(Employee $model, bool $paused)
    {
        $queueMember = $this->getBy('uuid' , $model->guid);
        if($queueMember === null){
            throw new \InvalidArgumentException("Not found queue member by [uuid = {$model->guid}]");
        }

        if($paused){
            $command = new QueuePauseAction($queueMember->interface);
        } else {
            $command = new QueueUnpauseAction($queueMember->interface);
        }

        /** @var $client ClientAMI */
        $client = resolve(ClientAMI::class);
        $client->open();

        $res = $client->send($command);
        logger_info("For queueMember[{$queueMember->membername}] has message - {$res->getKey('message')}");

        $client->close();
    }

    public function edit(Employee $model)
    {
        return $this->update($model->guid, $this->prepareDataForInsert($model));
    }

    public function remove(Employee $model): bool
    {
        $queue = $this->getBy('uuid', $model->guid);
        if(!$queue){
            logger_info("NOT FOUND QueueMember TO UUID [{$model->guid}]");
            return true;
        }

        $model->update(['is_insert_queue' => false]);

        if($res = $this->deleteBy('uuid', $model->guid)){
            logger_info("DELETE queue member [asterisk] SUCCESS [{$model->id}]");
        }

        return $res;
    }

    public function prepareDataForInsert(Employee $model): array
    {
        return [
            'queue_name' => $model->department->name,
            'uuid' => $model->guid,
            'membername' => $model->sip->number,
            'interface' => 'Local/' . $model->sip->number . '@queue_members',
            'state_interface' => 'Custom:' . $model->sip->number,
            'wrapuptime' => config('asterisk.queue_member.wrapuptime'),
            'ringinuse' => config('asterisk.queue_member.ringinuse'),
        ];
    }
}
