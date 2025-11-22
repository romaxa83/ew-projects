<?php

namespace App\Services\AA\Commands;

use App\Helpers\Logger\AALogger;
use App\Models\AA\AAResponse;
use App\Models\User\User;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;
use Illuminate\Support\Arr;

class CreateUser
{
    private string $path;

    public function __construct(
        protected RequestClient $client,
        protected ResponseService $responseService,
        protected UserService $userService,
    )
    {
        $this->path = config("aa.request.create_user.path");
    }

    public function handler(User $user): void
    {
        $user->refresh();
        try {
            $data = [
                'data' => [
                    'id' => '',
                    'name' => $user->name,
                    'number' => $user->phone->formatAA(),
                    'codeOKPO' => $user->egrpoy ?? '',
                    'email' => $user->email ? $user->email->getValue() : '',
                    'verified' => false,
                ]
            ];
            logger('CREATE USER', $data);
            $res = $this->client->postRequest($this->path, $data);

            logger("COMMAND CREATE USER [RESPONSE]", $res);

            $resObj = $this->responseService->save($res, $this->path, AAResponse::TYPE_CREATE_USER, $user);
//            TelegramDev::info("🔄 Ответ от АА по [{$resObj->type}], записан по ID [{$resObj->id}]", $user->name);

            $this->userService->completeFromAA($user, Arr::get($res, 'data'));
        }
        catch (AARequestException $e) {
            logger('CreateUser ERROR - ' . $e->getMessage(), [$e]);
            $this->responseService->save(json_to_array($e->getMessage()), $this->path, AAResponse::TYPE_CREATE_USER, $user, AAResponse::STATUS_ERROR);
//            TelegramDev::error(__FILE__, $e, $user->name);
        }
        catch (\Throwable $e){
            logger('CreateUser ERROR - ' . $e->getMessage(), [$e]);
//            TelegramDev::error(__FILE__, $e, $user->name);
            throw new AARequestException($e->getMessage(), $e->getCode());
        }
    }
}




