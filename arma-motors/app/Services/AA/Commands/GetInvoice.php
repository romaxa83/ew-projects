<?php

namespace App\Services\AA\Commands;

use App\Helpers\Logger\AALogger;
use App\Models\AA\AAResponse;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\Telegram\TelegramDev;

class GetInvoice
{
    private string $path;

    private $testRequest = [
        'ba8b6832-5742-11ec-8277-4cd98fc26f14',
    ];

    public function __construct(
        protected RequestClient $client,
        protected ResponseService $responseService,
    )
    {
        $this->path = config("aa.request.get_invoice.path");
    }

    public function handler(string $uuid)
    {
        $this->path .= $uuid;
        try {
            $res = $this->client->getRequest($this->path);

            AALogger::info("COMMAND GET INVOICE [REQUEST] , path {$this->path}");

            $resObj = $this->responseService->save($res, $this->path, AAResponse::TYPE_GET_INVOICE,);
//            TelegramDev::info("🔄 Ответ от АА по [{$resObj->type}], записан по ID [{$resObj->id}]", null, TelegramDev::LEVEL_IMPORTANT);

            AALogger::info("COMMAND GET INVOICE [RESPONSE]", $res);

            return $resObj;
        }
        catch (AARequestException $e) {
            $this->responseService->save(json_to_array($e->getMessage()), $this->path, AAResponse::TYPE_GET_INVOICE, null, AAResponse::STATUS_ERROR);
//            TelegramDev::error(__FILE__, $e, null);
            AALogger::info('COMMAND GET INVOICE [RESPONSE] - ERROR', json_to_array($e->getMessage()));
        }
        catch (\Throwable $e){
            $temp['message'] = $e->getMessage();
            $this->responseService->save( $temp, $this->path, AAResponse::TYPE_GET_INVOICE, null, AAResponse::STATUS_ERROR_IN_SAVE);

//            TelegramDev::error(__FILE__, $e, null);

            throw new AARequestException($e->getMessage(), $e->getCode());
        }
    }

    // todo данные для генерации счета (которые приходят из AA)
    // не удалять, используются в тестах
    public static function testData(): array
    {
        return [
            "parts" => [
                [
                    "sum" => 1135.3,
                    "ref" => "MR968274",
                    "discountedPrice" => 1135.3,
                    "name" => "ФІЛЬТР ПОВІТРЯНИЙ",
                    "price" => 1261.44,
                    "quantity" => "1",
                    "unit" => "шт",
                    "rate" => 9.999683,
                ],
                [
                    "sum" => 917.57,
                    "ref" => "7803A005",
                    "discountedPrice" => 917.57,
                    "name" => "ФІЛЬТР ПОВІТРЯНИЙ САЛОНА 7803A004 7803A109 TS200001",
                    "price" => 1019.52,
                    "quantity" => "1",
                    "unit" => "шт",
                    "rate" => 9.999804,
                ],
                [
                    "sum" => 3265.92,
                    "ref" => "4605A795",
                    "discountedPrice" => 3265.92,
                    "name" => "К-Т ГАЛЬМІВНИХ КОЛОДОК",
                    "price" => 3628.8,
                    "quantity" => "1",
                    "unit" => "шт",
                    "rate" => 10,
                ]
            ],
            "contactInformation" => "
                07400, Україна, Київська область, місто Бровари, вулиця Старотроїцька, будинок №42
                Тел. (044) 4902300, Факс
                р/с ФОП Барабаш Ю.О.UA773001190000026003095382001 (Гривна) в АТ \"БАНК АЛЬЯНС\", МФО:300119 код ЄДРПОУ 34356004",
            "date" => "13.09.2021",
            "organization" => "ФОП Барабаш Ю.О.",
            "number" => "VSK0150970",
            "shopper" => "Рильська Тетяна Олександрівна",
            "address" => "Світанкова, будинок №5",
            "phone" => "+380939838323",
            "etc" => "",
            "taxCode" => "",
            "discount" => 1208.76,
            "amountWithoutVAT" => 7266,
            "amountVAT" => 1211.02,
            "amountIncludingVAT" => 7266,
            "author" => "",
        ];
    }
}




