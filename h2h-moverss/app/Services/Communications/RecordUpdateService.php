<?php

namespace App\Services\Communications;

use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Order;

final class RecordUpdateService
{
    public static function client(
        Order $order,
        Client $client,
    ): void
    {
        CommunicationRecord::query()
            ->where('order_id', $order->id)
            ->each(function (CommunicationRecord $record) use ($client) {
                $record->client_id = $client->id;

                if($client->phones->count() > 0){
                    $phone = $client->phones[0]->value;
                    $record->channel_contact = $phone;
                } elseif($client->emails->count() > 0) {
                    $email = $client->emails[0]->value;
                    $record->channel_contact = $email;
                }

                $record->save();
            });
    }
}
