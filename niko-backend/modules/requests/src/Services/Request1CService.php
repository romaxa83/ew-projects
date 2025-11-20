<?php

namespace WezomCms\Requests\Services;

use Illuminate\Support\Facades\Http;
use WezomCms\Requests\ConvertData\OrderConvert;
use WezomCms\Requests\ConvertData\UserEditConvert;
use WezomCms\Requests\ConvertData\VerifyCarConvert;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\Models\Car;
use WezomCms\Users\Models\User;

class Request1CService
{
    private $url;
    private $login;
    private $password;

//    const VERIFY_CAR = '/VerifyByCar';
    const VERIFY_CAR = '/TestMobileAppProxy/hs/ApiV1/VerifyByCar';

//    const ORDER_TIME = '/GetSchedule';
    const ORDER_TIME = '/TestMobileAppProxy/hs/ApiV1/GetSchedule';

    const ORDER = '/TestMobileAppProxy/hs/ApiV1/Order';

    const USER_EDIT = '/TestMobileAppProxy/hs/ApiV1/AccountDataChanged';

    public function __construct()
    {
        $this->url = env('1C_BASE_URL');
        $this->login = env('1C_LOGIN');
        $this->password = env('1C_PASSWORD');
    }

    public function verifyCar(Car $car)
    {
        Telegram::event('запрос к 1с , на верификацию авто');

        $res = $this->send(self::VERIFY_CAR, VerifyCarConvert::toRequest($car));

        Telegram::event(serialize($res));

        return VerifyCarConvert::fromResponse($res);
    }

    public function orderTime(array $data)
    {
        Telegram::event('запрос на свободное время заявки');
        Telegram::event('data from as - ' . serialize($data));

        $res = $this->send(self::ORDER_TIME, $data);

        Telegram::event(serialize($res));

        return $res;
    }
//array (
//'ServiceTypeID' => 1,
//'StartDate' => 1113633000,
//'EndDate' => 1113654600,
//'AccountID' => 30,
//'DealerID' => 11,
//)
    public function order(ServicesOrder $order)
    {
        Telegram::event('запрос на отправку заявки (id ='. $order->id .') на сервисы');
        Telegram::event('Data: '. serialize(OrderConvert::toRequest($order)));

        $res = $this->send(self::ORDER, OrderConvert::toRequest($order));
        Telegram::event(serialize($res));

        return OrderConvert::fromResponse($res);
    }

    public function userEdit(User $user, $newPhone)
    {
        Telegram::event('запрос на редактирования пользователя (id ='. $user->id .')');

        $res = $this->send(self::USER_EDIT, UserEditConvert::toRequest($user, $newPhone));

        Telegram::event(serialize($res));

        return UserEditConvert::fromResponse($res);
    }

    private function send($uri, $data)
    {
//        dd($data);
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode( $this->login . ':' . $this->password )
            ])
                ->withoutVerifying()
                ->post($this->url . $uri, $data);

            if($response->status() != 200){
                Telegram::event('⚠️ ' . $response->status());
                Telegram::event('⚠️ ' . $response->body());
                logger($response->json());
                throw new \Exception('⚠️  Ошибка при запросе в 1с');
            }

            return $response->json();

        } catch (\Exception $exception){
            Telegram::event($exception->getMessage());
            logger($exception->getMessage());
            throw new \Exception($exception->getMessage());
        }
    }
}

