<?php

namespace App\Traits\Communications;

use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Twilio\TwilioSms;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait ClientHelper
{
    public function getClient(CommunicationRecord $model): ?Client
    {
        return match ($model->entity_type) {
            Client\Activity::MORPH_NAME => $this->hasClientRelation($model),
            TwilioSms::MORPH_NAME => $this->findClient($model),
            SmsEvents::MORPH_NAME => $this->findClient($model),
            CallsEvents::MORPH_NAME => $this->findClient($model),
            EventAfterCall::MORPH_NAME => $this->findClient($model),
            default => new \Exception("Trait ClientHelper not support: ". $model::class)
        };
    }

    public function getClients(CommunicationRecord $model): ?Collection
    {
        return match ($model->entity_type) {
            TwilioSms::MORPH_NAME => $this->findClients($model),
            SmsEvents::MORPH_NAME => $this->findClients($model),
            CallsEvents::MORPH_NAME => $this->findClients($model),
            EventAfterCall::MORPH_NAME => $this->findClients($model),
            default => null
        };
    }

    private function hasClientRelation(CommunicationRecord $model): Client
    {
        return $model->client
            ->load([
                'phones:id,client_id,value',
                'emails:id,client_id,value',
                'tags:id,title,color,icon',
                'notes:id,client_id,user_id,value',
                'notes.author:id,name'
            ])
            ->loadCount('orders')
            ;
    }

    private function findClientForRec(CommunicationRecord $model): ?Client
    {
        return Client::query()
            ->with([
                'phones:id,client_id,value',
                'emails:id,client_id,value',
                'tags:id,title,color,icon',
                'notes:id,client_id,user_id,value',
                'notes.author:id,name'
            ])
            ->withCount(['orders'])
            ->whereHas('phones', function (Builder $q) use ($model) {
                $q->where('value', 'LIKE', clear_phone($model->channel_contact));
            })
            ->first()
            ;
    }

    private function findClient(CommunicationRecord $model): ?Client
    {
        if($model->client){
            return $this->hasClientRelation($model);
        }

        return Client::query()
            ->with([
                'phones:id,client_id,value',
                'emails:id,client_id,value',
                'tags:id,title,color,icon',
                'notes:id,client_id,user_id,value',
                'notes.author:id,name'
            ])
            ->withCount(['orders'])
            ->whereHas('phones', function (Builder $q) use ($model) {
                $q->where('value', 'LIKE', clear_phone($model->channel_contact));
            })
            ->first()
            ;
    }

    private function findClients(CommunicationRecord $model): Collection
    {
        return Client::query()
            ->whereHas('phones', function (Builder $q) use ($model) {
                $q->where('value', 'LIKE', clear_phone($model->channel_contact));
            })
            ->get()
            ;
    }
}
