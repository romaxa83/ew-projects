<?php

namespace App\Services\AA\Commands;

use App\Models\AA\AAResponse;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\Telegram\TelegramDev;

class GetAct
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
        $this->path = config("aa.request.get_act.path");
    }

    public function handler(string $uuid)
    {
        $this->path .= $uuid;
        try {
            $res = $this->client->getRequest($this->path);

            $resObj = $this->responseService->save($res, $this->path, AAResponse::TYPE_GET_ACT,);
//            TelegramDev::info("🔄 Ответ от АА по [{$resObj->type}], записан по ID [{$resObj->id}]", null, TelegramDev::LEVEL_IMPORTANT);

            return $resObj;
        }
        catch (AARequestException $e) {
            $this->responseService->save(json_to_array($e->getMessage()), $this->path, AAResponse::TYPE_GET_ACT, null, AAResponse::STATUS_ERROR);
//            TelegramDev::error(__FILE__, $e, null);
        }
        catch (\Throwable $e){
            $temp['message'] = $e->getMessage();
            $this->responseService->save( $temp, $this->path, AAResponse::TYPE_GET_ACT, null, AAResponse::STATUS_ERROR_IN_SAVE);

//            TelegramDev::error(__FILE__, $e, null);

            throw new AARequestException($e->getMessage(), $e->getCode());
        }
    }

    // todo тестовые данные для генерации (которые приходят из AA)
    // не удалять, используются в тестах
    public static function testData(): array
    {
        return [
            "jobsAmountVAT" => "308,63",
            "payer" => [
                "name" => "Рильська Тетяна Олександрівна",
                "date" => "13.09.2021",
                "contract" => "Замовлення на обслуговування",
                "number" => "ARM0108925"
            ],
            "repairType" => "Ремонтні роботи",
            "number" => "ARM0108925",
            "closingDate" => "",
            "organization" => [
                "name" => "ФОП Барабаш Ю.О.",
                "phone" => "тел. ",
                "address" => "Україна, Київська область, м.Бровари, вул. Оболонська, будинок №72"
            ],
            "dealer" => "",
            "jobs" => [
                [
                    "ref" => "0008",
                    "name" => "Діагностика ходової частини",
                    "coefficient" => 0.5,
                    "priceWithVAT" => "823,00",
                    "priceWithoutVAT" => "685,83",
                    "amountWithoutVAT" => "257,18",
                    "price" => "514,36",
                    "amountIncludingVAT" => "308,62",
                    "rate" => 25.001215,
                ],
                [
                    "ref" => "312",
                    "name" => "Заміна повітряного фільтра",
                    "coefficient" => 0.2,
                    "priceWithVAT" => "823,00",
                    "priceWithoutVAT" => "685,83",
                    "amountWithoutVAT" => "102,87",
                    "price" => "514,35",
                    "amountIncludingVAT" => "123,45",
                    "rate" => 25,
                ],
                [
                    "ref" => "ЦБ0055445",
                    "name" => "Заміна повітряного фільтра салону",
                    "coefficient" => 0.3,
                    "priceWithVAT" => "823,00",
                    "priceWithoutVAT" => "685,83",
                    "amountWithoutVAT" => "154,28",
                    "price" => "514,27",
                    "amountIncludingVAT" => "185,14",
                    "rate" => 25.014176,
                ]
            ],
            "AmountInWords" => "Сім тисяч двісті шістдесят шість гривень нуль копійок",
            "date" => "13 вересня 2021 р.",
            "mileage" => 70557,
            "currentAccount" => "Р/р 26009056232699 в ПАТ КБ ПриватБанк иной в м.г. Киев МФО 380269    код ЄДРПОУ 3352611854",
            "owner" => [
                "name" => "Рильська Тетяна Олександрівна",
                "phone" => "+380939838323",
                "address" => "Світанкова, будинок №5",
                "email" => "нет",
                "etc" => "",
                "certificate" => "",
            ],
            "partsAmountIncludingVAT" => "5414,29",
            "customer" => [
                "name" => "Рильська Тетяна Олександрівна",
                "FIO" => "Рильська Т.О.",
                "phone" => "+380939838323",
                "email" => "нет",
                "date" => "",
                "number" => "",
            ],
            "model" => "OUTLANDER",
            "bodyNumber" => "JA4AD3A33HZ001924",
            "dateOfSale" => "03.06.2016",
            "stateNumber" => "AI8688IA",
            "producer" => "QB",
            "dispatcher" => [
                "position" => "Сервіс-консультант Митсубиси",
                "name" => "",
                "date" => "",
                "number" => "",
                "FIO" => "",
            ],
            "parts" => [
                [
                    "unit" => "шт",
                    "producer" => "MITSUBISHI",
                    "ref" => "MR968274",
                    "name" => "ФІЛЬТР ПОВІТРЯНИЙ",
                    "price" => "946,08",
                    "quantity" => "1,00",
                    "priceWithVAT" => "1261,44",
                    "priceWithoutVAT" => "1051,20",
                    "rate" => 9.999683,
                    "amountWithoutVAT" => "946,08",
                    "amountIncludingVAT" => "1135,30",
                ],
                [
                    "unit" => "шт",
                    "producer" => "MITSUBISHI",
                    "ref" => "7803A005",
                    "name" => "ФІЛЬТР ПОВІТРЯНИЙ САЛОНА 7803A004 7803A109 TS200001",
                    "price" => "764,64",
                    "quantity" => "1,00",
                    "priceWithVAT" => "1019,52",
                    "priceWithoutVAT" => "849,60",
                    "rate" => 9.999804,
                    "amountWithoutVAT" => "764,64",
                    "amountIncludingVAT" => "917,57",
                ],
                [
                    "unit" => "шт",
                    "producer" => "MITSUBISHI",
                    "ref" => "4605A795",
                    "name" => "К-Т ГАЛЬМІВНИХ КОЛОДОК",
                    "price" => "2721,60",
                    "quantity" => "1,00",
                    "priceWithVAT" => "3628,80",
                    "priceWithoutVAT" => "3024,00",
                    "rate" => 10,
                    "amountWithoutVAT" => "2721,60",
                    "amountIncludingVAT" => "3265,92",
                ]
            ],
            "disassembledParts" => "13.09.2021",
            "AmountIncludingVAT" => "7266,00",
            "recommendations" => "",
            "AmountVAT" => "1211,02",
            "discountParts" => "591,47",
            "discountJobs" => "617,29",
            "discount" => "1208,76",
            "jobsAmountWithoutVAT" => "1543,08",
            "jobsAmountIncludingVAT" => "1851,71",
            "partsAmountWithoutVAT" => "4511,90",
            "partsAmountVAT" => "902,39",
            "AmountWithoutVAT" => "6054,98",
        ];
    }
}





