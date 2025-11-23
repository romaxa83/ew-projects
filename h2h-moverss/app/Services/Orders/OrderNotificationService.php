<?php

namespace App\Services\Orders;

use App\Enums\Common\LogKeyEnum;
use App\Models\Attachment;
use App\Models\Order;
use App\Notifications\Orders\SendBolToClient;
use App\Notifications\Orders\SendBolToForeman;
use App\Notifications\Orders\SendEstimateToClient;
use App\Notifications\Orders\SendEstimateToForeman;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderNotificationService
{
    public function sendDocs(
        int $orderId,
        Attachment $attachment,
        string $type,
        array $attachments = []
    ): void
    {
        try {
            $order = Order::query()
                ->with(
                    'client.emails',
                    'works.dispatchEmployees.employee.user',
                )
                ->where('id', $orderId)
                ->first();


            if($type == 'estimate'){
                $this->sendEstimateHandler($order, $attachment);
            }
            if($type == 'bol'){
                $this->sendBolHandler($order, $attachment, $attachments);
            }

        } catch (\Throwable $e) {
            Log::error(LogKeyEnum::SendEmail().' FAIL', [
                'init_place' => 'App\Services\Orders\OrderNotificationService@sendDocs',
                'input' => [
                    'order_id' => $orderId,
                    'type' => $type,
                    'attachment' => $attachment,
                    'attachments' => $attachments,
                ],
                'exception' => $e,
            ]);
        }
    }

    private function sendEstimateHandler(Order $order, Attachment $attachment): void
    {
        if($order->client->emails->count() == 1){
            $emailClient = $order->client->emails[0]->value;
        } else {
            $emailClient = $order->client->emails->where('is_primary', 1)->first()?->value;
            if(is_null($emailClient)){
                $emailClient = $order->client->emails[0]->value ?? null;
            }
        }

        $foreman = null;
        if($order->mobileEstimate->estimateSignedEmployee){
            $foreman = $order->mobileEstimate->estimateSignedEmployee;
        } else {
            if(isset($order->works[0]) && isset($order->works[0]->dispatchEmployees)){
                foreach ($order->works[0]->dispatchEmployees ?? [] as $dispatchEmployees){
                    $user = $dispatchEmployees->employee->user;
                    if($user->isForeman()){
                        $foreman = $dispatchEmployees->employee;
                        break;
                    }
                }
            }
        }

        if($foreman->emails->count() == 1){
            $emailForeman = $foreman->emails[0]->value;
        } else {
            $emailForeman = $foreman->emails->where('is_primary', 1)->first()?->value;
            if(is_null($emailForeman)){
                $emailForeman = $foreman->emails[0]->value ?? null;
            }
        }

        if($emailClient){
            Notification::route('mail', $emailClient)
                ->notify(new SendEstimateToClient($order, $attachment));
        }

        if($foreman && $emailForeman){
            Notification::route('mail', $emailForeman)
                ->notify(new SendEstimateToForeman($order, $attachment, $foreman));

            // если форман является партнером, то отправляем письмо и его боссу
            if(
                $foreman->isPartner()
                && !$foreman->isPartnerHead()
            ){
                $head = $foreman->getPartnerHead();

                Notification::route('mail', $head->user->email)
                    ->notify(new SendEstimateToForeman($order, $attachment, $foreman));

                Log::notice(LogKeyEnum::SendEmail().'SEND BOL TO PARTNER HEAD ...', [
                    'init_place' => 'App\Services\Orders\OrderNotificationService@sendBolHandler',
                    'foremanClient' => $emailForeman,
                    'head' => $head,
                ]);
            }
        }

        Log::notice(LogKeyEnum::SendEmail().'SEND Estimate TO ...', [
            'init_place' => 'App\Services\Orders\OrderNotificationService@sendEstimateHandler',
            'emailClient' => $emailClient,
            'foremanClient' => $emailForeman,
        ]);
    }

    private function sendBolHandler(
        Order $order,
        Attachment $attachment,
        array $attachments = []
    ): void
    {
        if($order->client->emails->count() == 1){
            $emailClient = $order->client->emails[0]->value;
        } else {
            $emailClient = $order->client->emails->where('is_primary', 1)->first()?->value;
            if(is_null($emailClient)){
                $emailClient = $order->client->emails[0]->value ?? null;
            }
        }

        $foreman = null;
        if($order->mobileEstimate->bolSignedEmployee){
            $foreman = $order->mobileEstimate->bolSignedEmployee->user;
        } else {
            if(isset($order->works[0]) && isset($order->works[0]->dispatchEmployees)){
                foreach ($order->works[0]->dispatchEmployees ?? [] as $dispatchEmployees){
                    $user = $dispatchEmployees->employee->user;
                    if($user::inRole(User::ROLE_FOREMAN_ID)){
                        $foreman = $user;
                        break;
                    }
                }
            }
        }

        if($emailClient){
            Notification::route('mail', $emailClient)
                ->notify(new SendBolToClient($order, $attachment, $attachments));
        }

        if($foreman){
            Notification::route('mail', $foreman->email)
                ->notify(new SendBolToForeman($order, $attachment, $foreman, $attachments));

            // если форман является партнером, то отправляем письмо и его боссу
            if(
                $foreman->employee->isPartner()
                && !$foreman->employee->isPartnerHead()
            ){
                $head = $foreman->employee->getPartnerHead();

                Notification::route('mail', $head->user->email)
                    ->notify(new SendBolToForeman($order, $attachment, $foreman, $attachments));

                Log::notice(LogKeyEnum::SendEmail().'SEND BOL TO PARTNER HEAD ...', [
                    'init_place' => 'App\Services\Orders\OrderNotificationService@sendBolHandler',
                    'foremanClient' => $foreman->email,
                    'head' => $head,
                ]);
            }
        }

        Log::notice(LogKeyEnum::SendEmail().'SEND BOL TO ...', [
            'init_place' => 'App\Services\Orders\OrderNotificationService@sendBolHandler',
            'emailClient' => $emailClient,
            'foremanClient' => $foreman->email,
        ]);
    }
}
