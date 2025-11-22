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

class UpdateUser
{
    private string $path;

    public function __construct(
        protected RequestClient $client,
        protected ResponseService $responseService,
        protected UserService $userService,
    )
    {
        $this->path = config("aa.request.update_user.path");
    }

    public function handler(User $user): void
    {
        $user->refresh();
        try {
            $data = [
                'data' => [
                    'id' => $user->uuid !== null ? $user->uuid->getValue() : null,
                    'name' => $user->name,
                    'number' => null !== $user->new_phone ? $user->new_phone->formatAA() : $user->phone->formatAA(),
                    'codeOKPO' => $user->egrpoy ?? '',
                    'email' => $user->email ? $user->email->getValue() : '',
                    'verified' => $user->isVerify(),
                ]
            ];

            AALogger::info("UPDATE USER [REQUEST]", $data);

            $res = $this->client->postRequest($this->path, $data);

            $resObj = $this->responseService->save($res, $this->path, AAResponse::TYPE_UPDATE_USER, $user);
//            TelegramDev::info("🔄 Ответ от АА по [{$resObj->type}], записан по ID [{$resObj->id}]", $user->name);

            AALogger::info("COMMAND UPDATE USER [RESPONSE]", $res);

            $this->userService->completeFromAA($user, Arr::get($res, 'data'));
        }
        catch (AARequestException $e) {
            $this->responseService->save(json_to_array($e->getMessage()), $this->path, AAResponse::TYPE_UPDATE_USER, $user, AAResponse::STATUS_ERROR);
//            TelegramDev::error(__FILE__, $e, $user->name);
            AALogger::info('COMMAND UPDATE USER [RESPONSE] - ERROR', json_to_array($e->getMessage()));
        }
        catch (\Throwable $e){
//            TelegramDev::error(__FILE__, $e, $user->name);
            throw new AARequestException($e->getMessage(), $e->getCode());
        }
    }
}
