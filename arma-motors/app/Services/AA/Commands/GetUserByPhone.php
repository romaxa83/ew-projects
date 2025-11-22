<?php

namespace App\Services\AA\Commands;

use App\Events\User\NotUserFromAA;
use App\Helpers\Logger\AALogger;
use App\Models\AA\AAResponse;
use App\Models\User\User;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use App\Services\User\UserService;
use Illuminate\Support\Arr;

class GetUserByPhone
{
    private string $path;

    private $testPhones = [
        '+380443311080',
    ];

    public function __construct(
        protected RequestClient $client,
        protected ResponseService $responseService,
        protected UserService $userService,
        protected CarService $carService,
    )
    {
        $this->path = config("aa.request.get_user_by_phone.path");
    }

    public function handler(User $user)
    {
        $this->path .= $user->phone->formatAA();
        try {
            $res = $this->client->getRequest($this->path);

            AALogger::info("GET USER [REQUEST] , path {$this->path}");

            $resObj = $this->responseService->save($res, $this->path, AAResponse::TYPE_SIGNUP, $user);
//            TelegramDev::info("🔄 Ответ от АА по [{$resObj->type}], записан по ID [{$resObj->id}]", $user->phone->formatAA());

            AALogger::info("COMMAND GET USER BY PHONE [RESPONSE]", $res);

            $user = $this->userService->completeFromAA($user, Arr::get($res, 'data.user'));
            $this->carService->createFromAA($user, Arr::get($res, 'data.vechilces'));

            return $user;
        }
        catch (AARequestException $e) {
            logger('NOTUser ERROR - ' . $e->getMessage(), [$e]);

//            $this->responseService->save(json_to_array($e->getMessage()), $this->path, AAResponse::TYPE_SIGNUP, $user, AAResponse::STATUS_ERROR);
//            TelegramDev::error(__FILE__, $e, $user->name);

            event(new NotUserFromAA($user));
        }
        catch (\Throwable $e){
            $temp['message'] = $e->getMessage();
            $this->responseService->save( $temp, $this->path, AAResponse::TYPE_SIGNUP, $user, AAResponse::STATUS_ERROR_IN_SAVE);

//            TelegramDev::error(__FILE__, $e, $user->name);

            throw new AARequestException($e->getMessage(), $e->getCode());
        }
    }
}



