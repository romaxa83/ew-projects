<?php

namespace App\Services\Sips;

use App\Dto\Sips\SipDto;
use App\IPTelephony\Events\Subscriber\SubscriberUpdateOrCreateEvent;
use App\Models\Sips\Sip;
use App\Repositories\Sips\SipRepository;
use App\Services\AbstractService;

class SipService extends AbstractService
{
    public function __construct()
    {
        $this->repo = resolve(SipRepository::class);
        return parent::__construct();
    }

    public function create(SipDto $dto): Sip
    {
        $model = new Sip();

        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    public function update(Sip $model, SipDto $dto): Sip
    {
        $eventSubscriber = false;

        if($model->employee){
            $dto->number = $model->number;
        }

        $this->fill($model, $dto);

        if($model->isDirty('password') && $model->employee && $model->employee->hasSubscriberRecord()){
            $eventSubscriber = true;
        }

        $model->save();

        if($eventSubscriber){
            event(new SubscriberUpdateOrCreateEvent($model->employee));
        }

        return $model;
    }

    protected function fill(Sip $model, SipDto $dto): void
    {
        $model->password = $dto->password;
        $model->number = $dto->number;
    }
}
