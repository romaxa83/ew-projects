<?php

namespace App\Traits;

//use App\Models\Mailbox\Gmail\Message;

use App\Models\{Communications\ConversationMark,
    Division,
    Mailbox\Gmail\Message,
    Order,
    Order\Activity,
    Order\Notes,
    Order\Source,
    Order\Status,
    Ringostat\EventAfterCall,
    Tasks\Task,
    Twilio\TwilioSms,
    Zadarma\CallsEvents,
    Zadarma\SmsEvents};
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

trait ResponseFormatter
{
    //public $ResponseFormatterFromTimeZone =  null;

    /**
     * @param $Object
     * @param $ObjectDateTimezone - TimeZone of Object. Converting from this to UTC
     * @return object
     */
    public function getCommunicationPanelFormat($Object, $ObjectDateTimezone = null)
    {
        $ObjectDateTimezone = $ObjectDateTimezone ?? config('app.timezone');

        if (get_class($Object) == Task::class) {
            // таймзона или бранча задачи, или текущего бранча
            $Formatted = (object)[
                'type' => (new \ReflectionClass($Object))->getShortName(),
                'datetime' => new Carbon($Object->due_date, 'UTC'),
                'uid' => 'task-' . $Object->id,
                'item' => $Object
            ];
        } elseif (get_class($Object) == \App\Models\Client\Activity::class) {
            $Formatted = (object)[
                'type' => (new \ReflectionClass($Object))->getShortName(),
                'datetime' => new Carbon($Object->created_at, 'UTC'),
                'uid' => 'client-activity-' . $Object->id,
                'item' => $Object
            ];
        } elseif (get_class($Object) == Order::class) {
            $Formatted = (object)[
                'type' => (new \ReflectionClass($Object))->getShortName(),
                'datetime' => new Carbon($Object->created_at, 'UTC'),
                'uid' => 'order-' . $Object->id,
                'item' => $Object
            ];
        } elseif (get_class($Object) == SmsEvents::class) {
            $Formatted = (object)[
                'type' => (new \ReflectionClass($Object))->getShortName(),
                'datetime' => new Carbon($Object->created_at, 'UTC'),
                'uid' => 'sms-' . $Object->id,
                'item' => $Object
            ];
        } elseif (get_class($Object) == ConversationMark::class) {
            $Formatted = (object)[
                'type' => (new \ReflectionClass($Object))->getShortName(),
                'datetime' => new Carbon($Object->created_at, 'UTC'),
                'uid' => 'mark-' . $Object->id,
                'item' => $Object
            ];
        } elseif (get_class($Object) == TwilioSms::class) {
            $auditMetaData = null;
            if ($Audit = $Object->audits()->where('event', 'created')->first()) {
                if ($Audit->event == 'created' && $Audit->new_values['direction'] == 'outbound-api') {
                    $auditMetaData = array_filter($Audit->getMetaData(), function ($v, $k) {
                        $includeKeys = ['user_name', 'audit_event', 'user_id', 'user_type', 'user_name', 'user_email'];
                        return in_array($k, $includeKeys);
                    }, ARRAY_FILTER_USE_BOTH);
                }
            }
            $Formatted = (object)[
                'type' => (new \ReflectionClass($Object))->getShortName(),
                'datetime' => new Carbon($Object->created_at, 'UTC'),
                'uid' => 'sms-' . $Object->id,
                'audit' => $auditMetaData,
                'item' => $Object
            ];
        } elseif (get_class($Object) == Notes::class) {
            $Formatted = (object)[
                'type' => (new \ReflectionClass($Object))->getShortName(),
                'datetime' => new Carbon($Object->created_at, 'UTC'),
                'uid' => 'note-' . $Object->id,
                'item' => $Object
            ];
        } elseif (get_class($Object) == CallsEvents::class) {
//            $TimeZone = config('timezone');
//            if ($Order && $Order->division_id) {
//                $Division = Division::findOrFail($Order->division_id);
//                if (!empty($Division->miscs['tz']))
//                    $TimeZone = $Division->miscs['tz'];
//            }
            if ($Object->event == 'NOTIFY_END' && $Object->status_code == 16 && $Object->internal == null && $Object->disposition == 'answered') {
                $Object->disposition = 'voicemail';
            }
            $Formatted = (object)[
                'type' => (new \ReflectionClass($Object))->getShortName(),
                // timezone cast in config/app.php timezone
                'datetime' => (new Carbon($Object->call_start, $ObjectDateTimezone))->setTimezone('UTC'),
                'uid' => 'call-' . $Object->id,
                'item' => $Object
            ];
        } elseif (get_class($Object) == EventAfterCall::class) {
            $timestampSeconds = (int)($Object->call_timestamp / 1000000);
            $microseconds = $Object->call_timestamp % 1000000;
            $Carbon = Carbon::createFromTimestamp($timestampSeconds)->addMicroseconds($microseconds);
            $Formatted = (object)[
                'type' => (new \ReflectionClass($Object))->getShortName(),
                'datetime' => $Carbon,
                'uid' => 'ringostat-' . $Object->id,
                'item' => $Object
            ];

        }
        elseif (get_class($Object) == Message::class) {
            $auditMetaData = null;
            if ($Object->tags == 'sent' && $Audit = $Object->audits()->where('event', 'created')->first()) {
                if ($Audit->event == 'created') {
                    $auditMetaData = array_filter($Audit->getMetaData(), function ($v, $k) {
                        $includeKeys = ['user_name', 'audit_event', 'user_id', 'user_type', 'user_name', 'user_email'];
                        return in_array($k, $includeKeys);
                    }, ARRAY_FILTER_USE_BOTH);
                }
            }

            $Formatted = (object)[
                'type' => (new \ReflectionClass($Object))->getShortName(),
                'datetime' => new Carbon($Object->created_at, 'UTC'),
                'uid' => 'gmail-' . $Object->id,
                'audit' => $auditMetaData,
                'item' => $Object
            ];
        } elseif (get_class($Object) == Activity::class) {
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
            $type = (new \ReflectionClass($Object))->getShortName();
            $update = ['from' => ['title' => ''], 'to' => ['title' => '']];
            if ($Object->type == 'status') {
                if ($Object->miscs && !empty($Object->miscs['from'])) {
                    if ($from = $OrderStatuses->firstWhere('id', $Object->miscs['from']))
                        $update['from'] = [
                            'title' => $from->title,
                            'color' => $from->color,
                        ];
                }
                if ($Object->miscs && !empty($Object->miscs['to'])) {
                    if ($to = $OrderStatuses->firstWhere('id', $Object->miscs['to']))
                        $update['to'] = [
                            'title' => $to->title,
                            'color' => $to->color,
                        ];
                }
            } elseif ($Object->type == 'user') {
                if ($Object->miscs && !empty($Object->miscs['from'])) {
                    if ($from = $OrderManagers->firstWhere('id', $Object->miscs['from']))
                        $update['from'] = [
                            'title' => $from->name,
                        ];
                }
                if ($Object->miscs && !empty($Object->miscs['to'])) {
                    if ($to = $OrderManagers->firstWhere('id', $Object->miscs['to']))
                        $update['to'] = [
                            'title' => $to->name,
                        ];
                }
            } elseif ($Object->type == 'division') {
                if ($Object->miscs && !empty($Object->miscs['from'])) {
                    if ($from = $OrderDivisions->firstWhere('id', $Object->miscs['from']))
                        $update['from'] = [
                            'title' => $from->title,
                        ];
                }
                if ($Object->miscs && !empty($Object->miscs['to'])) {
                    if ($to = $OrderDivisions->firstWhere('id', $Object->miscs['to']))
                        $update['to'] = [
                            'title' => $to->title,
                        ];
                }
            } elseif ($Object->type == 'source') {
                if ($Object->miscs && !empty($Object->miscs['from'])) {
                    if ($from = $OrderSources->firstWhere('id', $Object->miscs['from']))
                        $update['from'] = [
                            'title' => $from->title,
                        ];
                }
                if ($Object->miscs && !empty($Object->miscs['to'])) {
                    if ($to = $OrderSources->firstWhere('id', $Object->miscs['to']))
                        $update['to'] = [
                            'title' => $to->title,
                        ];
                }
            }
            $Formatted = (object)[
                'type' => $type,
                'datetime' => new Carbon($Object->updated_at, 'UTC'),
                'uid' => 'activity-' . $Object->id,
                'item' => $Object,
                'update' => $update
            ];

        } else {
            dump('unknown getCommunicationPanelFormat class!');
            dd($Object);
        }
        $Formatted->timestamp = $Formatted->datetime->getTimestamp();
        return $Formatted;
        //$v->timestamp = $v->datetime->getTimestamp();
    }
}
