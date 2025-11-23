<?php

namespace App\Services\Telegram;

use App\Models\Attachment;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Log;

class TelegramService
{
    public function sendDocs($attachmentID, $type)
    {
        $attachment = Attachment::findOrFail($attachmentID);

        $orderId = $attachment->miscs['object']['id'];
        $order = Order::findOrFail($orderId);


        $currentDivision = session('division');
        $tz = !empty($currentDivision['miscs']['tz']) ? $currentDivision['miscs']['tz'] : config('app.timezone');

        $msg = '';
        $date = CarbonImmutable::now($tz)->format('M d, Y');
        $total = $order->mobileEstimate->bolTotalChargesAboveServices();

        $name = '';
        if(isset($order->works[0]) && isset($order->works[0]->dispatchEmployees[0])){
            $name = $order->works[0]->dispatchEmployees[0]->employee->full_name;
        }

        if($type == 'bol'){
            $msg .= "BOL {$name} / #{$orderId} / {$order->client->full_name} / {$date} / Total \${$total}";
        }
        if($type == 'estimate'){
            $msg .= "Estimate {$name} / #{$orderId} / {$order->client->full_name} / {$date}";
        }

        Log::info('Sending document to Telegram: ' . $attachment->hash);
        $data = [
            'chat_id' => config('app.tg.bot_documents_chat'),
            'caption' => $msg,
//            'caption' => 'Created new file for <a href="' . url('/orders/' . $attachment->miscs['object']['id']) . '">Order #' . $attachment->miscs['object']['id'] . '</a>',
            'document' => new \CURLFile(Storage::path($attachment->miscs['file']['patch'] . $attachment->hash), null, $attachment->miscs['file']['name'])
        ];
//dd($data, config('app.tg.bot_documents_hash'));
        $ch = curl_init('https://api.telegram.org/bot' . config('app.tg.bot_documents_hash') . '/sendDocument?parse_mode=HTML');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        Log::info('Telegram response: ' . $result);
        $http_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
    }


}
