<?php

namespace App\Services\AA\Commands;

use App\Events\Firebase\FcmPush;
use App\Helpers\Logger\AALogger;
use App\Models\AA\AAResponse;
use App\Models\Agreement\Agreement;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\Firebase\FcmAction;
use App\Services\Order\AgreementService;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use App\Types\Order\Status;

class AcceptAgreement
{
    private string $path;
    private bool $test;

    public function __construct(
        protected RequestClient $client,
        protected ResponseService $responseService,
        protected OrderService $orderService,
        protected AgreementService $agreementService
    )
    {
        $this->test = config("aa.request.accept_agreement.test");
        $this->path = config("aa.request.accept_agreement.path");
    }

    public function handler(Agreement $model): void
    {
        try {
            $data = $this->generateData($model);
            AALogger::info("COMMAND ACCEPT AGREEMENT [REQUEST] , path {$this->path}", $data);

            $res = $this->client->postRequest($this->path, $data);
            $resObj = $this->responseService->save($res, $this->path, AAResponse::TYPE_ACCEPT_AGREEMENT, $model->user);

//            TelegramDev::info(
//                "🔄 Ответ от АА по [{$resObj->type}], записан по ID [{$resObj->id}]",
//                $model->user->name,
//                TelegramDev::LEVEL_IMPORTANT
//            );

            $this->agreementService->setStatus($model, Agreement::STATUS_VERIFY);
//            $this->orderService->changeStatus($model->baseOrder, Status::create(Status::CREATED));

//            event(new FcmPush(
//                $model->user,
//                FcmAction::create(FcmAction::RECONCILIATION_WORK, [
//                    'class' => FcmAction::MODEL_AGREEMENT,
//                    'id' => $model->id
//                ], $model),
//                $model
//            ));
            AALogger::info("COMMAND ACCEPT AGREEMENT [RESPONSE]", $res);
        }
        catch (AARequestException $e) {
            $this->responseService->save(json_to_array($e->getMessage()), $this->path, AAResponse::TYPE_ACCEPT_AGREEMENT, $model->user, AAResponse::STATUS_ERROR);
            $this->agreementService->setErrorFromAA($model);
//            TelegramDev::error(__FILE__, $e, $model->user->name,TelegramDev::LEVEL_IMPORTANT);
        }
        catch (\Throwable $e) {
//            TelegramDev::error(__FILE__, $e, $model->user->name);
            throw new AARequestException($e->getMessage(), $e->getCode(), TelegramDev::LEVEL_IMPORTANT);
        }
    }

    public function generateData(Agreement $model): array
    {
        $id = $this->test
            ? "a6a2c5ef-a9cb-11ec-827c-4cd98fc26f11"
            : $model->uuid->getValue();

        return [
            'data' => [
                'id' => $id,
                'agreed' => true,
            ]
        ];
    }
}
