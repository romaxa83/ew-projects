<?php

namespace App\Jobs;

use App\Models\Attachment;
use App\Models\Order;
use App\Services\Telegram\Telegram;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Log;

class SendMobileAppDocumentToTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $attachmentID;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($attachmentID, $type = null)
    {
        $this->attachmentID = $attachmentID;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->old();
    }

    private function new()
    {
        Telegram::info("ALERT", null, [
            'alert_id' => 'f',
        ]);
    }

    private function old()
    {
        $attachment = Attachment::findOrFail($this->attachmentID);

        $orderId = $attachment->miscs['object']['id'];
        $order = Order::findOrFail($orderId);
        $order->load([
            'mobileEstimate.estimateSignedEmployee',
            'mobileEstimate.bolSignedEmployee',
        ]);


        $currentDivision = session('division');
        $tz = !empty($currentDivision['miscs']['tz']) ? $currentDivision['miscs']['tz'] : config('app.timezone');

        $msg = '';
        $date = CarbonImmutable::now($tz)->format('M d, Y');
        $total = $order->mobileEstimate->bolTotalChargesAboveServices();

        $name = '';
        if($this->type == 'bol'){
            if($order->mobileEstimate->bolSignedEmployee){
                $name = $order->mobileEstimate->bolSignedEmployee->full_name;
            } else {
                if(isset($order->works[0]) && isset($order->works[0]->dispatchEmployees[0])){
                    $name = $order->works[0]->dispatchEmployees[0]->employee->full_name;
                }
            }
            $msg .= "BOL {$name} / #{$orderId} / {$order->client->full_name} / {$date} / Total \${$total}";
        }

        if($this->type == 'estimate'){
            if($order->mobileEstimate->estimateSignedEmployee){
                $name = $order->mobileEstimate->estimateSignedEmployee->full_name;
            } else {
                if(isset($order->works[0]) && isset($order->works[0]->dispatchEmployees[0])){
                    $name = $order->works[0]->dispatchEmployees[0]->employee->full_name;
                }
            }
            $msg .= "Estimate {$name} / #{$orderId} / {$order->client->full_name} / {$date}";
        }

        Log::info('Sending document to Telegram: ' . $attachment->hash);
        $data = [
            'chat_id' => config('app.tg.bot_documents_chat'),
            'caption' => $msg,
//            'caption' => 'Created new file for <a href="' . url('/orders/' . $attachment->miscs['object']['id']) . '">Order #' . $attachment->miscs['object']['id'] . '</a>',
            'document' => new \CURLFile(Storage::path($attachment->miscs['file']['patch'] . $attachment->hash), null, $attachment->miscs['file']['name'])
        ];

        $ch = curl_init('https://api.telegram.org/bot' . config('app.tg.bot_documents_hash') . '/sendDocument?parse_mode=HTML');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if ($result === false) {
            // Получаем сообщение об ошибке cURL
            $error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            dd("Ошибка cURL: $error", "HTTP код: $http_code");
        } else {
            // Парсим ответ Telegram
//            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//            dd("HTTP код: $http_code", "Результат: ", json_decode($result, true));
        }



//        dd($result);
        Log::info('Telegram response: ' . $result);
        $http_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        // TODO catching errors
    }
}