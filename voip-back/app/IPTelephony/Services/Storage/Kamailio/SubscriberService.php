<?php

namespace App\IPTelephony\Services\Storage\Kamailio;

use App\IPTelephony\Entities\Kamailio\SubscriberEntity;
use App\Models\Employees\Employee;

class SubscriberService extends KamailioService
{
    public function getTable(): string
    {
        return SubscriberEntity::TABLE;
    }

    public function create(Employee $model)
    {
        if($res = $this->insert(
            $this->prepareDataForInsertSubscriber($model)
        )){
            Employee::query()->update(['is_insert_kamailio' => $res]);
        }
    }

    public function edit(Employee $model)
    {
        return $this->update($model->guid, $this->prepareDataForInsertSubscriber($model));
    }

    public function editOrCreate(Employee $model)
    {
        $subscriber = $this->getBy('uuid', $model->guid);

        if($subscriber){
            $this->edit($model);
        } else {
            $this->create($model);
        }
//        return $this->updateOrInsert(
//            $this->prepareDataForInsertSubscriber($model), [
//                'uuid' => $model->guid
//            ]
//        );
    }

    public function remove(Employee $model)
    {
        $subscriber = $this->getBy('uuid', $model->guid);
        if(!$subscriber){
            logger_info("NOT FOUND SUBSCRIBER TO UUID [{$model->guid}]");
            return true;
        }

        $model->update(['is_insert_kamailio' => false]);

        if($res = $this->delete($subscriber->id)){
            logger_info("DELETE subscriber [camailio] SUCCESS [{$model->id}]");
        }

        return $res;
    }
    public function prepareDataForInsertSubscriber(Employee $model): array
    {
        return [
            'username' => $model->sip->number,
            'domain' => config('kamailio.domain'),
            'password' => $model->sip->password,
            'context' => config('kamailio.context'),
            'uuid' => $model->guid,
            'full_name' => $model->getName()
        ];
    }
}
