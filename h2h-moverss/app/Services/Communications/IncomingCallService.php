<?php

namespace App\Services\Communications;

use App\Enums\ProviderEnum;
use App\Events\Communications\IncomingCallAnswerOrEnd;
use App\Events\Communications\IncomingCallStart;
use App\Models\Calls\IncomingCall;
use App\Models\Client\Phone;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Ringostat\EventBeforeCall;
use App\Models\Zadarma\CallsEvents;
use Illuminate\Database\Eloquent\Model;

/**
 * @see test \Tests\Unit\Services\Communications\IncomingCallService
*/
final class IncomingCallService
{
    public static function handler(Model $model): ?IncomingCall
    {
        $self = new self();

        return match ($model::class) {
            CallsEvents::class => $self->zadarma($model),
            EventBeforeCall::class => $self->ringostat($model),

            default => new \Exception("RecordCreateService Not support this: ".$model::class)
        };
    }

    public static function delete(Model $model): bool
    {
        $self = new self();

        return match ($model::class) {
            CallsEvents::class => $self->remove($model->pbx_call_id),
            EventAfterCall::class => $self->remove($model->call_id),
            EventBeforeCall::class => $self->remove($model->call_id),

            default => new \Exception("RecordCreateService Not support this: ".$model::class)
        };
    }

    private function remove($id): bool
    {
        if(
            $model = IncomingCall::query()
            ->where('call_id', $id)
            ->first()
        ){
            /** @var $model IncomingCall */
            broadcast(new IncomingCallAnswerOrEnd($model));

            logger_ringostat("[incoming-call] DELETE [{$id}]");

            return $model->delete();
        }

        return false;
    }

    private function zadarma($model): IncomingCall
    {
        /** @var $model CallsEvents */

        $data = [
            'provider' => ProviderEnum::Zadarma(),
            'call_id' => $model->pbx_call_id,
            'phone' => $model->destination,
        ];

        $phone = Phone::clearPhone($model->destination);
        if(
            $phone = Phone::query()
                ->where('value', "LIKE", $phone)
                ->first()
        ){
            $data['client_id'] = $phone->client_id;
        }

        return $this->create($data);
    }

    private function ringostat($model): IncomingCall
    {
        /** @var $model EventBeforeCall */

        $data = [
            'provider' => ProviderEnum::Ringostat(),
            'call_id' => $model->call_id,
            'phone' => $model->callers_number,
            'client_id' => $model->client_id,
        ];

        logger_ringostat('[incoming-call] CREATE', $data);

        return $this->create($data);
    }

    private function create(array $data): IncomingCall
    {
        $call = IncomingCall::create($data);

        broadcast(new IncomingCallStart($call));

        return $call;
    }
}
