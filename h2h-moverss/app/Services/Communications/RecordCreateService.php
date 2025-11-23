<?php

namespace App\Services\Communications;

use App\Enums\Clients\ActivityType;
use App\Enums\Common\LogKeyEnum;
use App\Enums\Communications\Type;
use App\Models\Client;
use App\Models\Client\Activity;
use App\Models\Communications\CommunicationRecord;
use App\Models\Communications\ConversationMark;
use App\Models\CommunicationsIgnoreList;
use App\Models\Division;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Order;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Tasks\Task;
use App\Models\Twilio\TwilioSms;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class RecordCreateService
{
    public static function handler(Model $model, array $additional = []): ?CommunicationRecord
    {
        $self = new self();

        $result = null;
        try {
            $result = match ($model::class) {
                Activity::class => $self->createFromClientActivity($model, $additional),
                TwilioSms::class => $self->createFromTwilio($model, $additional),
                SmsEvents::class => $self->createZadarmaSms($model, $additional),
                CallsEvents::class => $self->createZadarmaCall($model, $additional),
                EventAfterCall::class => $self->createFromRingostat($model, $additional),
                Message::class => $self->createFromGmailMessage($model, $additional),
                ConversationMark::class => $self->createFromConversationMark($model, $additional),
                Order::class => $self->createFromOrder($model, $additional),
                Order\Notes::class => $self->createFromOrderNote($model, $additional),
                Order\Activity::class => $self->createFromOrderActivity($model, $additional),
                Order\InventoryActivity::class => $self->createFromOrderInventoryActivity($model, $additional),
                Task::class => $self->createFromTask($model, $additional),
                default => new \Exception("RecordCreateService Not support this: ".$model::class)
            };

        } catch (\Throwable $e) {
            Log::error(LogKeyEnum::ComRec().' FAIL COM-PANEL CREATED', [
                'init_place' => 'App\Services\Communications\RecordCreateService@handler',
                'input' => [
                    'model' => $model,
                    'additional' => $additional,
                ],
                'error' => $e,
            ]);
        }

        return $result;
    }

    public static function updatedMessage(Message $model): ?CommunicationRecord
    {
        $rec = CommunicationRecord::query()
            ->where('entity_type', Message::MORPH_NAME)
            ->where('entity_id', $model->id)
            ->first();

        try {
            if($rec){
                $data['sort_at'] = $model->updated_at;
                if(CarbonImmutable::now()->subDay() > $data['sort_at']){
                    $data['is_answered'] = true;
                }
                $rec->update($data);
            }
        } catch (\Throwable $e) {
            Log::error(LogKeyEnum::ComRec().' FAIL COM-PANEL CREATED', [
                'init_place' => 'App\Services\Communications\RecordCreateService@updatedMessage',
                'input' => [
                    'model' => $model,
                ],
                'error' => $e,
            ]);
        }

        return $rec;
    }

    private function createFromTask(
        Task $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'type' => Type::Inner(),
            'sort_at' => $model->created_at,
            'division_id' => $model->division_id,
            'channel_contact' => null,
            'client_id' => $model->order?->client_id,
            'client_ids' => null,
            'is_answered' => true,
            'order_id' => $model->order_id,
        ], $additional);

        return $this->create($data);
    }

    private function createFromOrder(
        Order $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'type' => Type::Inner(),
            'sort_at' => $model->created_at,
            'division_id' => $model->division_id,
            'channel_contact' => null,
            'client_id' => $model->client_id,
            'client_ids' => null,
            'is_answered' => true,
            'order_id' => $model->id,
        ], $additional);

        return $this->create($data);
    }

    private function createFromOrderNote(
        Order\Notes $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'type' => Type::Inner(),
            'sort_at' => $model->created_at,
            'division_id' => $model->order?->division_id,
            'channel_contact' => null,
            'client_id' => $model->order?->client_id,
            'client_ids' => null,
            'is_answered' => true,
            'order_id' => $model->order_id,
        ], $additional);

        return $this->create($data);
    }

    private function createFromOrderActivity(
        Order\Activity $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'type' => Type::Inner(),
            'sort_at' => $model->updated_at,
            'division_id' => $model->order?->division_id,
            'channel_contact' => null,
            'client_id' => $model->order?->client_id,
            'client_ids' => null,
            'is_answered' => true,
            'order_id' => $model->order_id,
        ], $additional);

        return $this->create($data);
    }

    private function createFromOrderInventoryActivity(
        Order\InventoryActivity $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'type' => Type::Inner(),
            'sort_at' => $model->created_at,
            'division_id' => $model->order?->division_id,
            'channel_contact' => null,
            'client_id' => $model->order?->client_id,
            'client_ids' => null,
            'is_answered' => true,
            'order_id' => $model->order_id,
        ], $additional);

        $model = CommunicationRecord::create($data);

        return $model;
//        return $this->create($data);
    }

    private function createFromConversationMark(
        ConversationMark $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        $divisionId = null;
        if(isset($additional['division_id'])){
            $divisionId = $additional['division_id'];
        }

        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'type' => Type::Inner(),
            'sort_at' => $model->created_at,
            'division_id' => $divisionId,
            'channel_contact' => self::getChannelContact($model),
            'is_answered' => true,
        ], $additional);

        if($model->client_id){
            $data['client_id'] = $model->client_id;
        } elseif($model->contactTypeIsPhone()) {
            ['client_id' => $data['client_id'],'client_ids' => $data['client_ids']] =
                $this->clientQuery('phones', $data['channel_contact']);
        } elseif($model->contactTypeIsEmail()) {
            ['client_id' => $data['client_id'],'client_ids' => $data['client_ids']] =
                $this->clientQuery('emails', $data['channel_contact']);
        }

        return $this->create($data);
    }

    private function createFromGmailMessage(
        Message $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        if(isset($additional['division_id'])){
            $divisionId = $additional['division_id'];
        } else {
            $divisionId = $model->account?->division_id;
        }
        if(is_null($divisionId)) return null;

        // если email в черном списке не создаем
        $channelContact = self::getChannelContact($model);
        if(
            CommunicationsIgnoreList::email()->where('value', $channelContact)->exists()
        ){
            return null;
        }

        $type = null;
        if($model->isInbound()){
            $type = Type::Inbound();
        }
        if($model->isOutbound()){
            $type = Type::Outbound();
        }
        if(!$type) return null;

        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'type' => $type,
            'sort_at' => $model->updated_at->timestamp,
            'division_id' => $divisionId,
            'channel_contact' => $channelContact,
        ], $additional);

        if($model->isOutbound()){
            $data['is_answered'] = true;
        }

        $data = $this->detectClient($data);

        CommunicationRecord::query()
            ->where('entity_type', Message::MORPH_NAME)
            ->where('created_at', '<', CarbonImmutable::now())
            ->where('channel_contact', $channelContact)
            ->where('is_answered', false)
            ->update(['is_answered' => true]);

        return $this->create($data);
    }

    private function createFromClientActivity(
        Activity $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        if($model->type != ActivityType::Customer_inventory_save()){
            return null;
        }
        if(!isset($model->miscs['division_id'])){
            return null;
        }

        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'client_id' => $model->client_id,
            'type' => Type::Inner(),
            'sort_at' => $model->created_at,
            'division_id' => $model->miscs['division_id'],
            'channel_contact' => self::getChannelContact($model),
            'is_answered' => false,
        ], $additional);

        $model = CommunicationRecord::create($data);

        CommunicationRecord::query()
            ->where('entity_type', $model->entity_type)
            ->where('client_id', $model->client_id)
            ->where('is_answered', false)
            ->where('id', '!=', $model->id)
            ->update(['is_answered' => true])
        ;

        return $model;
//        return $this->create($data);
    }

    private function createFromTwilio(
        TwilioSms $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'sort_at' => $model->created_at,
            'division_id' => $model->division,
            'channel_contact' => self::getChannelContact($model),
            'type' => $model->direction === TwilioSms::INBOUND_VALUE
                ? Type::Inbound()
                : Type::Outbound(),
        ], $additional);

        if($model->direction === TwilioSms::OUTBOUND_VALUE){
            $data['is_answered'] = true;
        }

        $data = $this->detectClient($data);

        return $this->create($data);
    }

    private function createFromRingostat(
        EventAfterCall $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        if(!isset($additional['division_id'])) {
            $additional['division_id'] = \DB::table(Division::TABLE)
                ->select(['id', 'miscs'])
                ->whereJsonContains('miscs->ringostat_project_id', (string)$model->project_id)
                ->value('id')
            ;
        }

        if(is_null($additional['division_id'])){
            return null;
        }

        $sort_at = null;
        if($model->call_timestamp){
            $ms = $model->call_timestamp / 1000;
            // ???
            $sort_at = CarbonImmutable::createFromTimestampMs($ms, 'GMT');
        }

        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'sort_at' => $sort_at,
            'type' => $model->type === EventAfterCall::INBOUND_VALUE
                ? Type::Inbound()
                : Type::Outbound(),
            'channel_contact' => self::getChannelContact($model),
        ], $additional);

        if (
            $model->type === EventAfterCall::OUTBOUND_VALUE
            || ($model->type === EventAfterCall::INBOUND_VALUE && !$model->isNoAnswer())
        ) {
            $data['is_answered'] = true;
            if(
                $model->type === EventAfterCall::INBOUND_VALUE
                && $model->isVoicemail()
            ) {
                $data['is_answered'] = false;
            }
        }

        $data = $this->detectClient($data);

        return $this->create($data);
    }

    private function createZadarmaSms(
        SmsEvents $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        if(!isset($additional['division_id'])) {
            $additional['division_id'] = \DB::table(Division::TABLE)
                ->select(['id', 'miscs'])
                ->whereJsonContains('miscs->zadarma_pbx_id', (string)$model->pbx_id)
                ->value('id')
            ;
        }

        if(is_null($additional['division_id'])){
            return null;
        }

        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'sort_at' => $model->created_at,
            'channel_contact' => self::getChannelContact($model),
            'type' => $model->inbound
                ? Type::Inbound()
                : Type::Outbound(),
        ], $additional);

        if(!$model->inbound){
            $data['is_answered'] = true;
        }

        $data = $this->detectClient($data);

        return $this->create($data);
    }

    private function createZadarmaCall(
        CallsEvents $model,
        array $additional = []
    ): ?CommunicationRecord
    {
        if(
            !($model->event == CallsEvents::EVENT_NOTIFY_OUT_END
                || $model->event == CallsEvents::EVENT_NOTIFY_END)
        ){
            return null;
        }

        if(!isset($additional['division_id'])) {
            $additional['division_id'] = \DB::table(Division::TABLE)
                ->select(['id', 'miscs'])
                ->whereJsonContains('miscs->zadarma_pbx_id', (string)$model->pbx_id)
                ->value('id')
            ;
        }

        if(is_null($additional['division_id'])){
            return null;
        }

        $data = array_merge([
            'entity_id' => $model->id,
            'entity_type' => $model::MORPH_NAME,
            'sort_at' => $model->call_start,
            'channel_contact' => self::getChannelContact($model),
            'type' => Str::startsWith($model->pbx_call_id, 'in_')
                ? Type::Inbound()
                : Type::Outbound(),
        ], $additional);

        if(
            $model->event === CallsEvents::EVENT_NOTIFY_OUT_END
            || (
                $model->event === CallsEvents::EVENT_NOTIFY_END
                && $model->disposition == CallsEvents::DISPOSITION_ANSWERED
            )
        ){
            $data['is_answered'] = true;
        }

        $data = $this->detectClient($data);

        return $this->create($data);
    }

    private function create(array $attr): CommunicationRecord
    {
        // если приходит исходящая коммуникация, то проверяем если по
        // этому номеру не отвеченная входящая коммуникация и если есть
        // помечаем как прочитанная
        if(
            isset($attr['type'])
            && isset($attr['channel_contact'])
            && !is_null($attr['channel_contact'])
            && $attr['type'] === Type::Outbound()
        ){
            if(
                is_numeric($attr['sort_at'])
            ){
                $timestamp = (int)$attr['sort_at'];
                // Проверяем, что это целое число и лежит в допустимом диапазоне
                if ($timestamp > 0 && $timestamp <= PHP_INT_MAX) {
                    $sort = CarbonImmutable::createFromTimestamp($attr['sort_at']);
                }

            } else {
                $sort = $attr['sort_at'];
            }

            CommunicationRecord::query()
                ->where('channel_contact', $attr['channel_contact'])
                ->where('type', Type::Inbound())
                ->where('is_answered', false)
                ->where('sort_at', '<=', $sort)
                ->update(['is_answered' => true]);
        }

        $model = CommunicationRecord::create($attr);

        if(
            $attr['client_id'] !== null
            && (
                $attr['entity_type'] === Order::MORPH_NAME
                || $attr['entity_type'] === Order\Notes::MORPH_NAME
                || $attr['entity_type'] === Order\Activity::MORPH_NAME
                || $attr['entity_type'] === Order\InventoryActivity::MORPH_NAME
                || $attr['entity_type'] === ConversationMark::MORPH_NAME
                || $attr['entity_type'] === Task::MORPH_NAME
            )
        ) {
            $recs = CommunicationRecord::query()
                ->where('client_id', $attr['client_id'])
                ->where('is_answered', false)
                ->where('created_at', '<', CarbonImmutable::now())
                ->whereIn('entity_type', [
                    Message::MORPH_NAME,
                    EventAfterCall::MORPH_NAME,
                    TwilioSms::MORPH_NAME
                ])
                ->where('id', '!=', $model->id)
                ->get();

            foreach($recs as $rec){
                $rec->update(['is_answered' => true]);
            }
        }

        // если пришло письмо оно маркируется как не прочитанное но есть событие позже
        // (смотрим по сортировки т.к. письма могут приходить задним числом), то маркируем его как прочитаноне
        if(
            $model->isGmailMsg()
            && $model->is_answered === false
        ){
            if(
                $model->client_id
                && (
                CommunicationRecord::query()
                    ->where('client_id', $model->client_id)
                    ->where('sort_at', '>', $model->sort_at)
                    ->exists()
                )
            ){
                $model->update(['is_answered' => true]);
            } elseif (
                is_null($model->client_id)
                && (
                CommunicationRecord::query()
                    ->where('channel_contact', $model->channel_contact)
                    ->where('sort_at', '>', $model->sort_at)
                    ->exists()
                )
            ) {
                $model->update(['is_answered' => true]);
            }
        }

        return $model;
    }

    public static function getChannelContact($model)
    {
        if ($model instanceof CallsEvents) {
            if (Str::startsWith($model->pbx_call_id, 'in_') && !empty($model->caller_id)) {
                return clear_phone($model->caller_id);
            } elseif (Str::startsWith($model->pbx_call_id, 'in_') && empty($model->caller_id)) {
                return 'Anonymous-' . $model->id;
            } elseif (Str::startsWith($model->pbx_call_id, 'out_')) {
                $cleared = clear_phone($model->destination);
                if (strpos($cleared, '0002') === 0 || strpos($cleared, '0001') === 0) {
                    return clear_phone(substr($cleared, 4));
                }
                if (strpos($cleared, '888') === 0) {
                    return clear_phone(substr($cleared, 3));
                }
                return $cleared;
            }
        } elseif ($model instanceof EventAfterCall) {
            if ($model->type == 'in') {
                return clear_phone($model->caller_number);
            } elseif ($model->type == 'out') {
                return clear_phone($model->destination);
            }
        } elseif ($model instanceof TwilioSms) {
            if ($model->direction == 'outbound-api') {
                return clear_phone($model->to);
            } elseif ($model->direction == 'inbound') {
                return clear_phone($model->from);
            }
        } elseif ($model instanceof SmsEvents) {
            if ($model->inbound) {
                return clear_phone($model->caller_id);
            } else {
                return clear_phone($model->caller_did);
            }
        } elseif ($model instanceof Message) {
            if (!empty($model->miscs)) {
                if (!empty($model->miscs['from'])) {
                    if (isset($model->miscs['from']['email'])) {
                        return $model->miscs['from']['email'];
                    }
                    if (isset($model->miscs['from']['name'])) {
                        return $model->miscs['from']['name'];
                    }
                }
                if (!empty($model->miscs['to'])) {
                    $firstAddress = current($model->miscs['to']);
                    if (isset($firstAddress['email'])) {
                        return $firstAddress['email'];
                    }
                }
            }
        } elseif ($model instanceof ConversationMark) {
            return $model->contact_value;
        } elseif ($model instanceof Activity) {
            return $model->client_id;
        }

        throw new \Exception('getChannelContact. Unknown record type: ' . $model);
    }

    private function detectClient(array $data): array
    {
        if(!(isset($data['channel_contact']) && !is_null($data['channel_contact']))) return $data;

        if($data['entity_type'] == Message::MORPH_NAME){
            ['client_id' => $data['client_id'], 'client_ids' => $data['client_ids']]
                = $this->clientQuery('emails', $data['channel_contact']);
        } else {
            ['client_id' => $data['client_id'], 'client_ids' => $data['client_ids']]
                = $this->clientQuery('phones', $data['channel_contact']);
        }

        return $data;
    }

    public function clientQuery($relations, $contactValue): array
    {
        $data = [];

        $clientId = Client::query()
            ->select(['id'])
            ->whereHas($relations, function (Builder $q) use ($contactValue) {
                $q->where('value', 'LIKE', $contactValue);
            })
            ->value('id');

        $data['client_id'] = $clientId;

        if ($clientId) {
            $clientIds = Client::query()
                ->select(['id'])
                ->where('id', '!=', $clientId)
                ->whereHas($relations, function (Builder $q) use ($contactValue) {
                    $q->where('value', 'LIKE', $contactValue);
                })
                ->get()
                ->pluck('id')
                ->toArray();

            if (!empty($clientIds)) {
                $data['client_ids'] = $clientIds;
            }
        }

        if (!isset($data['client_ids'])) $data['client_ids'] = null;

        return $data;
    }
}
