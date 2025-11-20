<?php

namespace WezomCms\Firebase\UseCase;

use WezomCms\Firebase\Events\FcmNotificationEvent;
use WezomCms\Firebase\Types\FcmNotificationType;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\Users\Models\User;

class CallPushEvent
{
    public static function orderReject(ServicesOrder $order)
    {
        $order->load(['user', 'group']);
        app()->setLocale($order->user->lang);
        event(new FcmNotificationEvent(
            $order->user,
            [
                'title' => __('cms-firebase::site.notifications.order is reject.title', ['service_name' => $order->group->name]),
                'body' => __('cms-firebase::site.notifications.order is reject.body', ['service_name' => $order->group->name]),
            ],
            FcmNotificationType::ORDER,
            $order->id
        ));
    }

    public static function orderAccepted(ServicesOrder $order)
    {
        $order->load(['user', 'group']);

        event(new FcmNotificationEvent(
            $order->user,
            [
                'title' => __('cms-firebase::site.notifications.order is accepted.title', ['service_name' => $order->group->name]),
                'body' => __('cms-firebase::site.notifications.order is accepted.body', [
                    'service_name' => $order->group->name,
                    'date' => $order->final_date
                        ? $order->final_date->format('Y-m-d')
                        : $order->on_date->format('Y-m-d'),
                    'time' => $order->final_date
                        ? $order->final_date->format('H:i')
                        : $order->on_date->format('H:i'),
                ]),
            ],
            FcmNotificationType::ORDER,
            $order->id
        ));
    }

    public static function finalDateForOrder(ServicesOrder $order)
    {
        $order->load('user');

        event(new FcmNotificationEvent(
            $order->user,
            [
                'title' => __('cms-firebase::site.notifications.change final date for order.title', ['service_name' => $order->group->name]),
                'body' => __('cms-firebase::site.notifications.change final date for order.body', [
                    'date' => $order->final_date->format('H:i Y-m-d'),
                    'service_name' => $order->group->name
                ]),
            ],
            FcmNotificationType::ORDER,
            $order->id
        ));
    }

    public static function remindOrder(ServicesOrder $order)
    {
        event(new FcmNotificationEvent(
            $order->user,
            [
                'title' => __('cms-firebase::site.notifications.remind order.title', ['service_name' => $order->group->name]),
                'body' => __('cms-firebase::site.notifications.remind order.body', [
                    'date' => $order->final_date->format('H:i Y-m-d'),
                    'service_name' => $order->group->name,
                ]),
            ],
            FcmNotificationType::ORDER,
            $order->id
        ));
        $order->status_notify = true;
        $order->save();
    }

    public static function newPromotion(User $user)
    {
        event(new FcmNotificationEvent(
            $user,
            [
                'title' => __('cms-firebase::site.notifications.new promotion.title'),
                'body' => __('cms-firebase::site.notifications.new promotion.body'),
            ],
            FcmNotificationType::PROMOTION
        ));
    }

    public static function rateOrder(ServicesOrder $order)
    {
        $order->load('user');

        event(new FcmNotificationEvent(
            $order->user,
            [
                'title' => __('cms-firebase::site.notifications.rate order.title', ['service_name' => $order->group->name]),
                'body' => __('cms-firebase::site.notifications.rate order.body', ['service_name' => $order->group->name]),
            ],
            FcmNotificationType::ORDER,
            $order->id
        ));
    }

    public static function verifyCar(User $user)
    {
        event(new FcmNotificationEvent(
            $user,
            [
                'title' => __('cms-firebase::site.notifications.verify car.title'),
                'body' => __('cms-firebase::site.notifications.verify car.body'),
            ]
        ));
    }

    public static function test(User $user)
    {
//        self::registerTopic($user->fcm_token);
//        dd($user->fcm_token);

        $payload = array(
            'to' => $user->fcm_token,
            'priority'=>'high',
            "mutable_content"=>true,
            "notification"=>array(
                "title"=> 'test_niko',
                "body"=> 'test_niko'
            )
        );
        $headers = array(
            'Authorization:key=AAAAaVeBi_U:APA91bGjTXSgHjd_yM48yXWRAGHB22__MzbUxKxIlIJrXbbQgYjuCsSrvX47XAYQFYzG1CgdmRbPt6z7mFStSJoim82iDwRXKFMt8ycmyvcV-I-YxSOcuGRpOxUJeeXDj_lQ9-HS5F5l',
            'Content-Type: application/json'
            );

//        $ch = curl_init();
//        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
//        curl_setopt( $ch,CURLOPT_POST, true );
//        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
//        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
//        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
//        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $payload ) );
//        $result = json_decode(curl_exec($ch));
//        curl_close( $ch );
//
//        dd($result);


//        event(new FcmNotificationEvent(
//            $user,
//            [
//                'title' => __('тест __NIKO__'),
//                'body' => __('тест __NIKO__'),
//            ]
//        ));
    }

    public static function registerTopic($token)
    {
//        $url = 'https://iid.googleapis.com/iid/v1:batchAdd';
        $url = "https://iid.googleapis.com/iid/v1:batchRemove";
        $fields['registration_tokens'] = [$token];
        $fields['to'] = '/topics/niko-app';
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=AAAAaVeBi_U:APA91bGjTXSgHjd_yM48yXWRAGHB22__MzbUxKxIlIJrXbbQgYjuCsSrvX47XAYQFYzG1CgdmRbPt6z7mFStSJoim82iDwRXKFMt8ycmyvcV-I-YxSOcuGRpOxUJeeXDj_lQ9-HS5F5l'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = json_decode(curl_exec($ch));

        curl_close($ch);

        dd($result);
    }
}
