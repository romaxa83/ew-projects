<?php

namespace App\Services\Communications;

use App\Models\Attachment;
use App\Models\Client;
use App\Models\Client\Activity;
use App\Models\Communications\CommunicationRecord;
use App\Models\Communications\ConversationFavorites;
use App\Models\Communications\ConversationMark;
use App\Models\Division;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Order;
use App\Models\Order\Source;
use App\Models\Order\Status;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Tasks\Task;
use App\Models\Twilio\TwilioSms;
use App\Models\Vapi;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
// сервис для форматирования записей для коммуникационной панели
final class FormatterService
{
    public function recForMainPanelBase(CommunicationRecord $model, $tz = null)
    {
        $tz = $tz ?? config('app.timezone');

        if(is_null($model->entity)){
            \Log::error("CommunicationRecord entity is null !!!", [
                'model' => $model,
            ]);
            return [];
        }

        return match ($model->entity_type) {
            Activity::MORPH_NAME => $this->getClientActivityData($model),
            TwilioSms::MORPH_NAME => $this->getTwilioData($model),
            SmsEvents::MORPH_NAME => $this->getZadarmaSmsData($model),
            CallsEvents::MORPH_NAME => $this->getZadarmaCallData($model, $tz),
            EventAfterCall::MORPH_NAME => $this->getRingostatData($model, $tz),
            ConversationMark::MORPH_NAME => $this->getConversationMarkData($model, $tz),
            Order::MORPH_NAME => $this->getOrderData($model, $tz),
            Message::MORPH_NAME => $this->getMessageData($model, $tz),
            Order\Notes::MORPH_NAME => $this->getOrderNoteData($model, $tz),
            Order\Activity::MORPH_NAME => $this->getOrderActivityData($model, $tz),
            Order\InventoryActivity::MORPH_NAME => $this->getOrderInventoryActivityData($model, $tz),
            Task::MORPH_NAME => $this->getTaskData($model, $tz),
            Vapi\CallEvent::MORPH_NAME => $this->getVapiCallData($model, $tz),
            default => null
        };
    }

    public function recForMainPanel(
        CommunicationRecord $model,
        $tz = null,
        $searchTerm = null,
    )
    {
        $res = $this->recForMainPanelBase($model, $tz);

        if (!$res){
            new \Exception("Not support this: ".$model::class);
        }

        $res['client'] = $this->getClient($model);
        $res['collectionClients'] = $this->getClients($model);
        $res['channelContact'] = $model->channel_contact;
        $res['starred'] = $this->isStarredRec($res);
        $res['isAnswered'] = $model->is_answered;

        $res = array_merge($res, $this->getManagers($model));

        if($searchTerm){
            $res['findedByText'] = $this->getFindedByText(
                $searchTerm,
                $res['client'],
                $res['channelContact']
            );
        }

        return $res;
    }

    private function getAttachments($model): array
    {
        $model->load(['medias']);

        $tmp = [];
        foreach ($model->medias as $media) {
            /** @var $media Attachment */
            $tmp[] = [
                'url' => $media->getUrl(),
                'name' => $media->miscs['file']['name'] ?? null,
                'size' => $media->miscs['file']['size'] ?? null
            ];
        }

        return $tmp;
    }

    private function getVapiCallData(CommunicationRecord $model): array
    {
        $model->entity->load([
            'clientRequest.client'
        ]);

        $date = new CarbonImmutable($model->entity->call_start_at);

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'item' => $model->entity,
        ];
    }

    private function getMessageData(CommunicationRecord $model): array
    {
        $date = new CarbonImmutable($model->entity->created_at, 'UTC');

        $auditMetaData = null;
        if (
            $model->entity->tags == Message::TAG_SENT
            && $audit = $model->entity->audits()->where('event', 'created')->first()
        ) {

            $auditMetaData = array_filter($audit->getMetaData(), function ($v, $k) {
                $includeKeys = [
                    'user_name',
                    'audit_event',
                    'user_id',
                    'user_type',
                    'user_name',
                    'user_email'
                ];
                return in_array($k, $includeKeys);
            }, ARRAY_FILTER_USE_BOTH);
        }

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'audit' => $auditMetaData,
            'item' => $model->entity,
        ];
    }

    private function getZadarmaSmsData(CommunicationRecord $model): array
    {
        $date = new CarbonImmutable($model->entity->created_at, 'UTC');

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'item' => $model->entity,
        ];
    }

    private function getConversationMarkData(CommunicationRecord $model): array
    {
        $model->entity->load(['user', 'user.employee']);

        $date = new CarbonImmutable($model->entity->created_at, 'UTC');

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'item' => $model->entity,
        ];
    }

    private function getTaskData(CommunicationRecord $model): array
    {
        $model->entity->load([
            'type',
            'status',
            'subscribers',
            'author:id,name,email',
            'executor:id,name,email',
            'author.employee:id,name,l_name',
            'executor.employee:id,name,l_name',
        ]);

        $date = new CarbonImmutable($model->entity->due_date, 'UTC');

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'item' => $model->entity,
        ];
    }

    private function getOrderData(CommunicationRecord $model): array
    {
        $date = new CarbonImmutable($model->entity->created_at, 'UTC');

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'item' => $model->entity,
        ];
    }

    private function getOrderInventoryActivityData(CommunicationRecord $model): array
    {
        $date = new CarbonImmutable($model->entity->created_at, 'UTC');

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'item' => $model->entity->load(['client', 'user']),
        ];
    }

    private function getOrderNoteData(CommunicationRecord $model): array
    {
        $date = new CarbonImmutable($model->entity->created_at, 'UTC');

        $item = $model->entity->load([
            'order',
            'author:id,name',
            'author.employee:id,name,l_name'
        ]);
        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'item' => $item,
        ];
    }

    private function getOrderActivityData(CommunicationRecord $model): array
    {
        $OrderStatuses = Cache::remember('panelOrderStatuses', 5 * 60, function () {
            return Status::all();
        });
        $OrderManagers = Cache::remember('panelOrderManagers', 5 * 60, function () {
            return User::with('employee')->get(['id', 'name']);
        });
        $OrderSources = Cache::remember('panelOrderSources', 5 * 60, function () {
            return Source::all(['id', 'title']);
        });
        $OrderDivisions = Cache::remember('panelOrderDivisions', 5 * 60, function () {
            return Division::all(['id', 'title']);
        });

        /** @var $entity Order\Activity */
        $entity = $model->entity;

        $update = ['from' => ['title' => ''], 'to' => ['title' => '']];
        if($entity->isStatusType()){
            if ($entity->miscs && !empty($entity->miscs['from'])) {
                if ($from = $OrderStatuses->firstWhere('id', $entity->miscs['from']))
                    $update['from'] = [
                        'title' => $from->title,
                        'color' => $from->color,
                    ];
            }
            if ($entity->miscs && !empty($entity->miscs['to'])) {
                if ($to = $OrderStatuses->firstWhere('id', $entity->miscs['to']))
                    $update['to'] = [
                        'title' => $to->title,
                        'color' => $to->color,
                    ];
            }
        }
        if($entity->isUserType()){
            if ($entity->miscs && !empty($entity->miscs['from'])) {
                if ($from = $OrderManagers->firstWhere('id', $entity->miscs['from']))
                    $update['from'] = [
                        'title' => $from->name,
                    ];
            }
            if ($entity->miscs && !empty($entity->miscs['to'])) {
                if ($to = $OrderManagers->firstWhere('id', $entity->miscs['to']))
                    $update['to'] = [
                        'title' => $to->name,
                    ];
            }
        }
        if($entity->isDivisionType()){
            if ($entity->miscs && !empty($entity->miscs['from'])) {
                if ($from = $OrderDivisions->firstWhere('id', $entity->miscs['from']))
                    $update['from'] = [
                        'title' => $from->title,
                    ];
            }
            if ($entity->miscs && !empty($entity->miscs['to'])) {
                if ($to = $OrderDivisions->firstWhere('id', $entity->miscs['to']))
                    $update['to'] = [
                        'title' => $to->title,
                    ];
            }
        }
        if($entity->isSourceType()){
            if ($entity->miscs && !empty($entity->miscs['from'])) {
                if ($from = $OrderSources->firstWhere('id', $entity->miscs['from']))
                    $update['from'] = [
                        'title' => $from->title,
                    ];
            }
            if ($entity->miscs && !empty($entity->miscs['to'])) {
                if ($to = $OrderSources->firstWhere('id', $entity->miscs['to']))
                    $update['to'] = [
                        'title' => $to->title,
                    ];
            }
        }

        $date = new CarbonImmutable($model->entity->created_at, 'UTC');

        return [
            'id' => $model->id,
            'type' => self::getType($entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($entity),
            'item' => $entity,
            'update' => $update,
        ];
    }

    private function getRingostatData(CommunicationRecord $model): array
    {
        if(is_null($model->entity)){
            return [];
        }

        $timestampSeconds = (int)($model->entity->call_timestamp / 1000000);
        $microseconds = $model->entity->call_timestamp % 1000000;
        $date = Carbon::createFromTimestamp($timestampSeconds)->addMicroseconds($microseconds);

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'item' => $model->entity,
        ];
    }

    private function getZadarmaCallData(CommunicationRecord $model, $tz): array
    {
        if (
            $model->entity->event == CallsEvents::EVENT_NOTIFY_END
            && $model->entity->status_code == 16
            && $model->entity->internal == null
            && $model->entity->disposition == CallsEvents::DISPOSITION_ANSWERED
        ) {
            $model->entity->disposition = CallsEvents::DISPOSITION_VOICEMAIL;
        }

        $tz = str_replace('\\', '', $tz);

        $date = (new CarbonImmutable($model->entity->call_start, $tz))
            ->setTimezone('UTC');

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            // timezone cast in config/app.php timezone
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'item' => $model->entity,
        ];
    }

    private function getClientActivityData(CommunicationRecord $model): array
    {
        $date = new CarbonImmutable($model->entity->created_at, 'UTC');

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'item' => $model->entity,
        ];
    }

    private function getTwilioData(CommunicationRecord $model): array
    {
        $auditMetaData = null;

        if ($audit = $model->entity->audits()->where('event', 'created')->first()) {
            if ($audit->event == 'created' && $audit->new_values['direction'] == TwilioSms::OUTBOUND_VALUE) {
                $auditMetaData = array_filter($audit->getMetaData(), function ($v, $k) {
                    $includeKeys = ['user_name', 'audit_event', 'user_id', 'user_type', 'user_name', 'user_email'];
                    return in_array($k, $includeKeys);
                }, ARRAY_FILTER_USE_BOTH);
            }
        }

        $date = new CarbonImmutable($model->entity->created_at, 'UTC');

        $model->entity->load(['statuses']);

        return [
            'id' => $model->id,
            'type' => self::getType($model->entity),
            'datetime' => $date,
            'timestamp' => $date->timestamp,
            'uid' => self::getUid($model->entity),
            'audit' => $auditMetaData,
            'item' => $model->entity,
            'attachments' => $this->getAttachments($model->entity),
        ];
    }

    public function getClient(CommunicationRecord $model): ?Client
    {
        if($model->client){
            return $model
                ->client
                ->load([
                    'phones:id,client_id,value',
                    'emails:id,client_id,value',
                    'tags:id,title,color,icon',
                    'notes:id,client_id,user_id,value',
                    'notes.author:id,name',
                ])
                ->loadCount('orders')
                ;
        }

        return null;
    }

    public function getClients(CommunicationRecord $model): ?Collection
    {
        if($model->client_ids){
            Client::query()
                ->whereIn('id', $model->client_ids)
                ->whereHas('phones', function (Builder $q) use ($model) {
                    $q->where('value', 'LIKE', clear_phone($model->channel_contact));
                })
                ->get();
        }

        return null;
    }

    public function getManagers(CommunicationRecord $model): array
    {
        $managersIds = [];
        $managerAbbr = null;

        if($model->client){
            $managersIds = $model
                ->client
                ->orders()
                ->orderBy('id', 'DESC')
                ->groupBy('user_id')
                ->get(['user_id'])
                ->pluck('user_id')
                ->toArray()
            ;

            if(!empty($managersIds)){
                $managerAbbr = User::whereIn('id', $managersIds)
                    ->get(['name'])
                    ->pluck('name')
                    ->first();
            }
        }

        return [
            'managers' => $managersIds,
            'managerAbbr' => $managerAbbr
        ];
    }

    private function isStarredRec(array $data): bool
    {
        $MarkRecord = null;
        if ($data['client']) {
            $MarkRecord = ConversationFavorites::where('client_id', $data['client']->id)
                ->where('user_id', \Auth::id())
                ->first();
        } elseif (
            $data['item'] instanceof CallsEvents
            || $data['item'] instanceof  TwilioSms
        ) {
            $MarkRecord = ConversationFavorites::byPhone()
                ->where('contact_value', $data['channelContact'])
                ->where('user_id', \Auth::id())
                ->first();
        }

        if ($MarkRecord)
            return !empty($MarkRecord->starred);

        return false;
    }

    public static function getType(Model $model): string
    {
        return (new \ReflectionClass($model))->getShortName();
    }

    public static function getUid(Model $model): ?string
    {
        $prev = match ($model::MORPH_NAME ) {
            Activity::MORPH_NAME => 'client-activity-',
            TwilioSms::MORPH_NAME => 'sms-',
            SmsEvents::MORPH_NAME => 'sms-',
            CallsEvents::MORPH_NAME => 'call-',
            EventAfterCall::MORPH_NAME => 'ringostat-',
            ConversationMark::MORPH_NAME => 'mark-',
            Message::MORPH_NAME => 'gmail-',
            Order::MORPH_NAME => 'order-',
            Order\Notes::MORPH_NAME => 'note-',
            Order\Activity::MORPH_NAME => 'activity-',
            Order\InventoryActivity::MORPH_NAME => 'activity-inventory-',
            Task::MORPH_NAME => 'task-',
            Vapi\CallEvent::MORPH_NAME => 'vapi-call-',
            default => null
        };

        return $prev . $model->id;
    }

    public function getFindedByText(
        string $searchTerm,
        ?Client $client,
        ?string $contact = null,
    ): string
    {
        $text = null;

        if($contact){
            $this->textFindedByText(
                'Contact: ',
                $contact,
                $searchTerm,
                $text
            );
        }

        if($client){

            $this->textFindedByText(
                'CustomerID: ',
                $client->id,
                $searchTerm,
                $text
            );

            $this->textFindedByText(
                'Name: ',
                Str::lower($client->full_name),
                $searchTerm,
                $text
            );

            foreach ($client->phones as $phone){
                $this->textFindedByText(
                    'Phone: +1',
                    $phone->value,
                    $searchTerm,
                    $text
                );
            }

            foreach ($client->emails as $email){
                $this->textFindedByText(
                    'Email: ',
                    $email->value,
                    $searchTerm,
                    $text
                );
            }

            foreach ($client->orders()->get('id') as $order){
                $this->textFindedByText(
                    'Order: #',
                    $order->id,
                    $searchTerm,
                    $text
                );
            }
        }

        return trim($text);
    }

    private function textFindedByText(
        string $title,
        string $field,
        string $searchTerm,
        &$text
    ): void
    {
        if(\Str::contains($field, $searchTerm)){
            $text .= $title . Str::replaceFirst($searchTerm, '<mark>' . $searchTerm . '</mark>', $field) . '. ';
        }
    }
}
