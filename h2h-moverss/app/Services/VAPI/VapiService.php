<?php

namespace App\Services\VAPI;

use App\Models\Vapi\CallEvent;
use App\Models\Vapi\ClientRequest;
use App\Services\Communications\RecordCreateService;
use App\Services\Requests\VAPI\Commands\Assistants\GetAssistants;
use App\Services\Requests\VAPI\Commands\Calls\GetCall;

class VapiService
{
    public function createCallEventFromWebhook(array $data): CallEvent
    {
        $model = new CallEvent();
        $model->call_id = $data['message']['call']['id'];
        $model->type_call = $data['message']['call']['type'];
        $model->caller_number = $data['message']['customer']['number'];
        $model->type_event = $data['message']['type'];
        $model->recording_url = $data['message']['artifact']['recordingUrl'] ?? null;
        $model->call_start_at = $data['message']['startedAt'] ?? null;
        $model->call_end_at = $data['message']['endedAt'] ?? null;
        $model->reason_ended = $data['message']['endedReason'];
        $model->duration = $data['message']['durationSeconds'] ?? 0;
        $model->misc = $data;

        $model->save();

        if(
            $clientReq = ClientRequest::query()
                ->whereNull('call_rec_id')
                ->where('caller_number', $model->caller_number)
                ->first()
        ){
            $clientReq->update([
                'call_rec_id' => $model->id
            ]);
        }

        RecordCreateService::handler($model);

        return $model;
    }

    public function createClientRequest(array $data): ClientRequest
    {
        $model = new ClientRequest();
        $model->caller_number = $data['caller_number'];
        $model->department_type = $data['department_type'];
        $model->client_name = $data['client_full_name'] ?? null;
        $model->client_number = $data['client_phone_number'] ?? null;
        $model->call_back_at = $data['call_back_at'] ?? null;
        $model->pickup_location = $data['pickup_location'] ?? null;
        $model->pickup_stairs = $data['pickup_stairs'] ?? null;
        $model->delivery_location = $data['delivery_location'] ?? null;
        $model->delivery_stairs = $data['delivery_stairs'] ?? null;
        $model->additional = $data['additional'] ?? null;

        $callerNumber = clear_phone($data['caller_number']);
        if(
            $client = \App\Models\Client::query()
                ->select(['id'])
                ->whereHas('phones', function ($q) use ($callerNumber) {
                    $q->where('value', $callerNumber);
                })
                ->first()
        ){
            $model->client_id = $client->id;
        }

        $model->misc = $data;

        $model->save();

        return $model;
    }


    public function getAssistantList(): array
    {
        try {
            /** @var GetAssistants $command */
            $command = resolve(GetAssistants::class);
            $command->setLogger(\Log::channel('vapi'));

            return $command->exec();

        } catch (\Exception $e) {}


        return [];
    }

    public function getCall(string $id): array
    {
        try {
            /** @var GetAssistants $command */
            $command = resolve(GetCall::class);
            $command->setLogger(\Log::channel('vapi'));

            return $command->exec([
                'id' => $id,
            ]);

        } catch (\Exception $e) {}


        return [];
    }
}
