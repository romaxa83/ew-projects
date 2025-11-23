<?php

namespace App\Http\Controllers\Ringostat;

use App\Events\Communications\EmployeeStatus;
use App\Http\Controllers\Controller;
use App\Models\Client\Phone;
use App\Models\Communications\CallInfo;
use App\Models\Employee;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Ringostat\EventBeforeCall;
use App\Services\Communications\IncomingCallService;
use App\Services\Communications\RecordCreateService;
use Illuminate\Http\Request;

/**
 * для получения информации по пользователю на созвоне он или нет.
 * Если сотрудник звонит - получаем данные из события BeforeOutCall, тип
 * у звонка будет out, связь с клиентом будет через поле - callers_number (значение sip_direction),
 * а клиента в поле destination
 * Если звонят сотруднику, то по событию BeforeCall мы создаем запись звонка, клиент берем
 * из поля callers_number, а сотрудник привязываем после события TakingCall, так как звонок изначально
 * приходит на номер проекта, а потом распредиляется на сотрудника, в событии TakingCall есть call_id звонка,
 * по которому мы найдем запись BeforeCall, так же есть поле employee_id (это id рингостата, но они прописаны у сотрудников)
 * по которому привяжем сотрудника
*/
class WebhookController extends Controller
{
    // перед звонком
    public function handleBeforeCall(Request $request)
    {
        logger_ringostat('[webhook] HANDLER_BEFORE_CALL', $request->all());

        $this->createEventBeforeCall(
            array_merge($request->all(), ['from_event' => 'before_call'])
        );

        return response()->json(['message' => 'Event recorded successfully'], 201);
    }

    // после звонка
    public function handleAfterCall(Request $request)
    {
        $data = $request->all();
        logger_ringostat('[webhook] HANDLER_AFTER_CALL', $data);

        $this->createEventAfterCall($data);

        return response()->json(['message' => 'Event recorded successfully'], 201);
    }

    // момент поднятие трубки
    public function handleTakingCall(Request $request)
    {
        $data = $request->all();
        logger_ringostat('[webhook] HANDLER_TAKING_CALL', $data);

        $employee = Employee::query()
            ->where('ringostat_id', $data['employee_id'])
            ->first()
        ;
        $event = EventBeforeCall::query()
            ->where('call_id', $data['call_id'])
            ->first();

        if($employee && $event){
            $employee->update([
                'ringostat_call_rec_id' => $event->id,
                'callers_number' => $event->callers_number,
            ]);

            logger_ringostat('[webhook] HANDLER_TAKING_CALL' ,[
                'msg' => "Update employee [ringostat_call_rec_id => {$event->id}] as in a call"
            ]);
            IncomingCallService::delete($event);

            // произошла внутренняя переадресация, удаляем данные по звонку у переадресовавшего
            if(
                $anotherEmployee = Employee::query()
                    ->whereNotNull('ringostat_call_rec_id')
                    ->where('id', '!=', $employee->id)
                    ->where('callers_number', $event->callers_number)
                    ->first()
            ){
                $anotherEmployee->update([
                    'callers_number' => null,
                    'ringostat_call_rec_id' => null,
                ]);

                broadcast(new EmployeeStatus($employee));
            }
        }

        return response()->json(['message' => 'Event recorded successfully'], 201);
    }

    // перед исходящим звонком
    public function handleBeforeOutCall(Request $request)
    {
        logger_ringostat('[webhook] HANDLER_BEFORE_OUT_CALL', $request->all());

        $this->createEventBeforeCall(
            array_merge($request->all(), ['from_event' => 'before_out_call'])
        );

        return response()->json(['message' => 'Event recorded successfully'], 201);
    }

    // после исходящего звонка
    public function handleAfterOutCall(Request $request)
    {
        $data = $request->all();

        logger_ringostat('[webhook] HANDLER_AFTER_OUT_CALL', $data);

        $this->createEventAfterCall($data);

        return response()->json(['message' => 'Event recorded successfully'], 201);
    }

    public function handleLocationForwarding(Request $request)
    {
        logger_ringostat('[webhook] HANDLER_LOCATION_FORWARDING', $request->all());

        return response()->json(['message' => 'Event recorded successfully'], 201);
    }

    public function handleCallProcessedAi(Request $request)
    {
        $data = $request->all();

        try {
            make_transaction(function () use ($data) {
                $more = 10 * 60; // 10 min
                if($data['call_duration'] >= $more){
                    $calls = EventAfterCall::query()
                        ->where('call_id', $data['call_id'])
                        ->get();

                    foreach ($calls as $call) {
                        $call->dialogue_quality_score = $data['dialogue_quality_score'] ?? null;
                        $call->dialogue_quality_details = $data['dialogue_quality_details'] ?? null;
                        $call->call_card_link = $data['call_card_link'] ?? null;
                        $call->save();

                        if($communicationRec = $call->communicationRecord){
                            if(
                                !CallInfo::query()
                                    ->where('call_id', $data['call_id'])
                                    ->where('channel_contact', $communicationRec->channel_contact)
                                    ->exists()
                            ){
                                $callInfo = new \App\Models\Communications\CallInfo();
                                $callInfo->channel_contact = $communicationRec->channel_contact;
                                $callInfo->client_id = $communicationRec->client_id;
                                $callInfo->call_id = $data['call_id'];
                                $callInfo->score = $data['dialogue_quality_score'];
                                $callInfo->details = $data['dialogue_quality_details'];
                                $callInfo->save();
                            }
                        }
                    }
                }
            });


            logger_ringostat_ai('[webhook AI] HANDLER_CALL_PROCESSED_AI', [
                'data' => $data
            ]);
        } catch (\Throwable $e) {

            logger_ringostat('[webhook AI] HANDLER_CALL_PROCESSED_AI', [
                'data' => $data
            ]);
        }

        return response()->json(['message' => 'Event recorded successfully'], 201);
    }

    private function createEventAfterCall(array $data): void
    {
        try {
            $eventData = [];

            foreach ($data as $key => $value) {
                if (in_array($key, (new EventAfterCall)->getFillable())) {
                    $eventData[$key] = $value;
                }
            }

            $model = EventAfterCall::create($eventData);

            if(
                $employee = Employee::query()
                    ->where('ringostat_id', $model->employee_id)
                    ->whereNotNull('ringostat_call_rec_id')
                    ->first()
            ){
                $employee->update([
                    'ringostat_call_rec_id' => null,
                    'callers_number' => null,
                ]);

                broadcast(new EmployeeStatus($employee));
            }

            RecordCreateService::handler($model);

            // удаляется запись для звонков которые еще не отвечены
            IncomingCallService::delete($model);
        } catch (\Throwable $e) {
            logger_ringostat('[webhook] createEventAfterCall FAIL!!!!!', ['error' => $e]);
        }
    }

    private function createEventBeforeCall(array $data): void
    {
        try {
            $model = EventBeforeCall::create($data);

            if($data['call_type'] == 'out'){
                $value = Phone::clearPhone($data['destination']);
                if(
                    $phone = Phone::query()
                        ->where('value', "LIKE", $value)
                        ->first()
                ){
                    $model->update(['client_id' => $phone->client_id]);
                }

                $employee = Employee::query()
//                    ->whereJsonContains('ringostat_miscs->sip_direction', $data['callers_number'])
                    ->where('ringostat_miscs', 'like', '%'. $data['callers_number'] .'%')
                    ->first();
                if($employee){
                    $employee->update([
                        'ringostat_call_rec_id' => $model->id,
                        'callers_number' => $model->callers_number
                    ]);

                    broadcast(new EmployeeStatus($employee));
                }
            }
            if($data['call_type'] == 'in'){
                $value = Phone::clearPhone($data['callers_number']);
                if(
                    $phone = Phone::query()
                        ->where('value', "LIKE", $value)
                        ->first()
                ){
                    $model->update(['client_id' => $phone->client_id]);
                }

                logger_ringostat('[webhook] createEventBeforeCall', ['model' => $model->toArray()]);

                // создается запись для звонков которые еще не отвечены
                IncomingCallService::handler($model);
            }
        } catch (\Throwable $e) {
            logger_ringostat('[webhook] createEventBeforeCall FAIL!!!!!', ['error' => $e]);
        }
    }
}
