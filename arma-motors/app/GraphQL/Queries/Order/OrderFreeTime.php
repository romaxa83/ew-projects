<?php

namespace App\GraphQL\Queries\Order;

use App\GraphQL\BaseGraphQL;
use App\Helpers\DateTime;
use App\Services\Order\OrderFreeTimeService;
use App\Services\Telegram\TelegramDev;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderFreeTime extends BaseGraphQL
{
    public function __construct(protected OrderFreeTimeService $service)
    {}

    /**
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {

        try {
            TelegramDev::info($this->reqDataForDebug($args['input']), null, TelegramDev::LEVEL_IMPORTANT);
            $this->validation($args['input'], $this->rules());

            $freeTime = $this->service->getFreeTimes($args['input']);

//            dd($freeTime);

//            TelegramDev::info($this->resDataForDebug($freeTime), null, TelegramDev::LEVEL_IMPORTANT);
            return $freeTime;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }

    private function rules(): array
    {
        return [
            'date' => ['required', 'numeric', 'digits:13'],
        ];
    }

    private function reqDataForDebug($data)
    {
        $dateHuman = DateTime::fromMillisecondToDate($data['date']);
        $date = $data['date'];
        $dealershipId = $data['dealershipId'];
        $serviceId = $data['serviceId'];

        return "REQUEST FREE TIME \n Date human - {$dateHuman} \n Date - {$date} \n DealershipID - $dealershipId \n ServiceID - $serviceId";
    }

    private function resDataForDebug($data)
    {
        $res = "FREE TIME \n";
        foreach ($data as $k => $item){
            $time = DateTime::timeFromMillisecond($item);
            $res .= "{$k}: {$time} ({$item}) \n";
        }

        return $res;
    }
}

