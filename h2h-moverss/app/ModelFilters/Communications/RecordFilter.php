<?php

namespace App\ModelFilters\Communications;

use App\Enums\Communications\Filter\EntityEnum;
use App\Enums\Communications\Filter\PeriodEnum;
use App\ModelFilters\BaseModelFilter;
use App\Models\Client\Activity;
use App\Models\Communications\CommunicationRecord;
use App\Models\Communications\ConversationFavorites;
use App\Models\Employee;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Order;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Twilio\TwilioSms;
use App\Models\Vapi;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin CommunicationRecord
*/
class RecordFilter extends BaseModelFilter
{
    public const ALL = 'all';

    public const CONTACTS_MY_CLIENT = 'myclients';
    public const CONTACTS_UNASSIGNED = 'unassigned';

    public const STARRED_STARRED = 'starred';
    public const STARRED_NOT_STARRED = 'notstarred';

    public function channels(array $value): void
    {
        if(empty($value)) return;

        $this->where(function (Builder $query) use ($value) {
            return $query->whereIn('entity_type', $value);
        });
    }

    public function entity(string $value): void
    {
        $data = [];
        if($value == EntityEnum::All()) {
            $data = [
                TwilioSms::MORPH_NAME,
                EventAfterCall::MORPH_NAME,
                CallsEvents::MORPH_NAME,
                SmsEvents::MORPH_NAME,
                Activity::MORPH_NAME,
                Message::MORPH_NAME,
                Vapi\CallEvent::MORPH_NAME
            ];
        }
        if($value == EntityEnum::Emails()) {
            $data = [
                Message::MORPH_NAME
            ];
        }

        if($value == EntityEnum::Calls()) {
            $data = [
                TwilioSms::MORPH_NAME,
                EventAfterCall::MORPH_NAME,
                CallsEvents::MORPH_NAME,
                SmsEvents::MORPH_NAME,
                Vapi\CallEvent::MORPH_NAME
            ];
        }

        $this->where(function (Builder $query) use ($data) {
            return $query->whereIn('entity_type', $data);
        });
    }

    public function communications(string $value): void
    {
        if($value == self::ALL) return;

        $this->where('is_answered', false);
    }

    public function responsible(array $value): void
    {
        $managers = Employee::whereIn('id', $value)
            ->get(['id', 'auth_user_id']);

        $clients = Order::whereHas('manager', function ($q) use ($managers) {
            return $q->whereIn('id', $managers->pluck('auth_user_id')->toArray());
        })
            ->where('client_id', '>', 0)
            ->groupBy('client_id')
            ->get('client_id')
        ;

        $this->whereIn('client_id', $clients->pluck('client_id')->toArray());
    }

    public function period(string $value): void
    {
        $to = CarbonImmutable::now('UTC');

        if($value == PeriodEnum::Today()) {
            $from = CarbonImmutable::now('UTC')->startOfDay();
        }
        if($value == PeriodEnum::Yesterday()) {
            $from = CarbonImmutable::now('UTC')->subDays(1)->startOfDay();
            $to = CarbonImmutable::now('UTC')->subDays(1)->endOfDay();
        }
        if($value == PeriodEnum::Last_7_days()) {
            $from = CarbonImmutable::now('UTC')->subDays(7)->startOfDay();
        }
        if($value == PeriodEnum::Last_30_days()) {
            $from = CarbonImmutable::now('UTC')->subDays(30)->startOfDay();
        }
        if($value == PeriodEnum::Any()) {
            return;
        }

        $this->whereBetween('sort_at', [$from, $to]);
    }

    public function contacts(string $value): void
    {
        if($value === self::ALL) return;

        if($value === self::CONTACTS_MY_CLIENT) {
            $myClients = Order::query()
                ->select(['client_id'])
                ->where('user_id', \Auth::id())
                ->where('client_id', '>', 0)
                ->groupBy('client_id')
                ->pluck('client_id')
                ->toArray()
            ;

            $this->whereIn('client_id', $myClients);
        }

        if($value === self::CONTACTS_UNASSIGNED) {
            $this->whereNull('client_id');
        }
    }

    public function starred(string $value): void
    {
        if($value === self::ALL) return;

        $starredRecIds = ConversationFavorites::query()
            ->select(['communication_rec_id'])
            ->where('user_id', \Auth::id())
            ->whereNotNull('communication_rec_id')
            ->where('starred', true)
            ->get()
            ->pluck('communication_rec_id')
            ->toArray()
        ;

        if($value === self::STARRED_STARRED) {
            $this->whereIn('id', $starredRecIds);
        }

        if($value === self::STARRED_NOT_STARRED) {
            $this->whereNotIn('id', $starredRecIds);
        }
    }

    public function searchTerm(string $value): void
    {
        $value = strip_tags($value);
        preg_match('#([a-z]+)#i', $value, $ma);
        $num = !$ma
            ? preg_replace('/[^0-9]/', '', $value)
            : '';


        $this->where(function (Builder $query) use ($value, $num) {
            $query
                ->whereHas('client', function (Builder $query) use ($value, $num) {
                $query
                    ->whereRaw("CONCAT(`name`, ' ', `lname`) LIKE ?", ['%' . $value . '%'])
                    ->orWhere(function (Builder $query) use ($value, $num) {
                        $query
                            ->whereHas('emails', function ($query) use ($value) {
                                $query->where('value', 'like', '%' . $value . '%');
                            })
                            ->when($num, function ($query) use ($num) {
                                $query->orWhereHas('phones', function ($q) use ($num) {
                                    $q->where('value', 'like', '%' . $num . '%');
                                });
                            })
                        ;
                    })
                ;
            })
            ;
        })
            ->orWhere(function ($query) use ($num) {
                $clientId = Order::query()
                ->select('client_id')
                ->where('id', $num)
                ->first()?->client_id
            ;
            $query->when($clientId, function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            });
        })
        ;
    }
}
