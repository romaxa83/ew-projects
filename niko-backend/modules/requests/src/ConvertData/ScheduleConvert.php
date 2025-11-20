<?php

namespace WezomCms\Requests\ConvertData;

use Carbon\Carbon;
use WezomCms\Core\UseCase\DateFormatter;
use WezomCms\Dealerships\Models\Dealership;
use WezomCms\Dealerships\Models\Schedule;
use WezomCms\Services\Repositories\ServiceGroupRepository;
use WezomCms\Services\Types\ServiceType;
use WezomCms\TelegramBot\Telegram;

class ScheduleConvert
{
    public static function toRequest($serviceType, $userId, $dealershipId, $timestamp,?int $modelId): array
    {
        /**
         * @todo убать в проде
         * 1615445522
         * $startDate и $endDate формируеться для тестового метода получения занятых временых меток в 1с
         * на проде или когда метод будет рабочий в StartDate и EndDate нужно подставить приходщий $timestamp
         */
//        $d = Carbon::createFromTimestamp($timestamp)->toDate()->format('Y-m-d');
//        $dayStart = Carbon::createFromFormat('Y-m-d H:i:s', $d . ' 00:00:00')->timestamp;

//        dd($timestamp / 1000);

//        $timestamp = DateFormatter::convertFor1c($timestamp);

        // тестовый вариант
//        $startDate = $timestamp + 34200; // 9:30
//        $endDate = $timestamp + 55800; // 15:30

        $startDate = $timestamp; // 0:00
        $endDate = $timestamp + 86399; // 23:59

        return  [
            "ServiceTypeID" => (int)$serviceType,
            "StartDate" => $startDate,
            "EndDate" => $endDate,
            "AccountID" => (int)$userId,
            "DealerID" => (int)$dealershipId,
            "ModelID" => $modelId
        ];
    }

    public static function fromResponse($data, $timestamp, $dealershipId, $serviceType)
    {
        return self::calculateFreeTime($data, $timestamp, $dealershipId, $serviceType);
    }

    /**
     * расчитывает свободные врменые метки (9:30, 10:00, 10:30, ....) для заявок,
     * на основе расписания рабочего времени дц и данных,
     * пришедшим из 1с по занятым временым меткам,
     * шаг для меток - 30 мин. (1800 сек.)
     * @throws \Exception
     */
    private static function calculateFreeTime($data, $timestamp, $dealershipId, $serviceType)
    {
        // данные, по занятым временым меткам, пришедшие из 1с
        $responseData = $data;
//dd($responseData);
//        $responseData = [
//            "Data" => [
//                [ 1617211800, 1617213600],
//            ]
//        ];
        // 30 мин
        $diapason = 1800;
        // дата пришедшее из мп
        $dataFromApp = $timestamp;
        //1616889600000
        // получаем день недели, чтоб получить расписание работа на этот день в дц
        $dayOfWeek = Carbon::createFromTimestamp($dataFromApp)->dayOfWeekIso;
        // получаем расписание на этот день
        $dealership = Dealership::query()->with('schedule')->where('id', $dealershipId)->first();

        $scheduleDc = $dealership->getScheduleWhereDayNumber(self::getScheduleType($serviceType))[$dayOfWeek];
        // нет расписание на этот день (или выходные или не заполненно в дц)
        if(empty($scheduleDc)){
            Telegram::event('Невозможно сформировать свободное время, у дц ('. $dealership->name .') нет графика работы, на данные день');
            return [];
        }

        // формируем все возможное временые метки с шагов в полчас
        $allFreeTime = [];
        for($i = $scheduleDc[0]; $i < last($scheduleDc); $i = ($i + $diapason)){
            $allFreeTime[] = $i;
        }

        // из занятых диапазонов времени, получены от 1с, получаем временые метки
        $busyTimes = [];
        foreach ($responseData["Data"] ?? [] as $key => $items) {
            foreach ($items as $k => $item){
                $busyTimes[$key][$k] = self::getSecond($item);
            }
        }

        // удаляем временые метки , для об. перерывов дц, если есть
        if(isset($scheduleDc[1]) && isset($scheduleDc[2])){
            $timeWithoutBreak = [];
            foreach ($allFreeTime as $key => $freeTime){
                if(($freeTime >= $scheduleDc[1]) && ($freeTime < $scheduleDc[2])){

                } else {
                    $timeWithoutBreak[$key] = $freeTime;
                }
            }
            $allFreeTime = array_values($timeWithoutBreak);
        }

        // очищаем все временые метки от занятых
        foreach ($busyTimes as $busyTime){
            foreach ($allFreeTime as $key => $freeTime){
                if(($freeTime >= $busyTime[0]) && ($freeTime < $busyTime[1])){
                    unset($allFreeTime[$key]);
                }
            }
        }

        if(config('cms.telegram-bot.bot.telegram_use')){
            // массив полученого времени, в часах, для отладки
            $humanTimeForDev = [];
            $humanTimeNumForDev = [];
            foreach($allFreeTime as $key => $time){
                $t = $timestamp + $time;
                $humanTimeNumForDev[$key] = $t;
                $humanTimeForDev[$key] = Carbon::createFromTimestamp($t)->toDateTimeString();
            }

            Telegram::event('Сформированы свободные временые метки');
            Telegram::event(serialize($humanTimeNumForDev));
            Telegram::event(serialize($humanTimeForDev));
        }

        foreach ($allFreeTime ?? [] as $key => $time){
            $timeRes = $timestamp + $time;
            $allFreeTime[$key] = DateFormatter::convertTimestampForFront($timeRes);
        }

        return array_values($allFreeTime);
    }

    private static function getSecond($timestamp)
    {
        $d = Carbon::createFromTimestamp($timestamp)->toDate()->format('Y-m-d');
        $dayStart = Carbon::createFromFormat('Y-m-d H:i:s', $d . ' 00:00:00', 'GMT')->timestamp;

        return $timestamp - $dayStart;
    }

    /**
     * @param $serviceType
     * @return int|null
     * @throws \Exception
     */
    private static function getScheduleType($serviceType)
    {
        $scheduleType = null;
        if(ServiceType::isSto($serviceType)){
            $scheduleType = Schedule::TYPE_SERVICE;
        }

        if(ServiceType::isTestDrive($serviceType)){
            $scheduleType = Schedule::TYPE_SALON;
        }

        if($scheduleType == null){
            throw new \Exception('Opps ,not correct seriveType');
        }

        return $scheduleType;
    }
}
