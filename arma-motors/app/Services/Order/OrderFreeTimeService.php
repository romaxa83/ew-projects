<?php

namespace App\Services\Order;

use App\Helpers\DateTime;
use App\Helpers\Logger\OrderLogger;
use App\Models\AA\AAOrderPlanning;
use App\Models\AA\AAPost;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Models\Dealership\Department;
use App\Models\Dealership\TimeStep;
use App\Models\Order\Order;
use App\Models\User\Car;
use App\Repositories\Catalog\Service\ServiceRepository;
use App\Repositories\Dealership\DealershipRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\BaseService;
use App\Services\Order\Exceptions\OrderFreeTimeException;
use App\Types\Order\Status;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class OrderFreeTimeService extends BaseService
{
    protected $step = 30;

    public function __construct(
        protected DealershipRepository $dealershipRepository,
        protected ServiceRepository $serviceRepository,
        protected OrderRepository $orderRepository,
    )
    {}

    public function getFreeTimes(array $data): array
    {
        OrderLogger::info('FREE TIME - INPUT', $data);
        /** @var $dealership Dealership */
        $dealership = $this->dealershipRepository->getByID(
            $data['dealershipId'], ["posts"]
        );

        /** @var $service Service */
        $service = $this->serviceRepository->findByID($data['serviceId']);

        $step = $service->time_step ?: Service::DEFAULT_TIME_STEP;

        // получаем день для которого нужно сформировать временные метки
        $day = DateTime::fromMillisecondToCarboneImutableDate($data['date'])->today();

        if($dealership->posts->isEmpty()){
            throw new \Exception("Not set post by dealership [{$dealership->alias}]");
        }

        $tmp = [];
        // получаем все посты для данного дц
        foreach ($dealership->posts as $post){
            /** @var $post AAPost */
            $schedule = $post->schedules()->where('date', $day)->first();

            // если расписание для данного поста есть и это не выходной день
            if($schedule && $schedule->work_day){

                // кол-во минут с начала дня до начала рабочего дня
                $minStartDay = $schedule->start_work->diffInMinutes($day);
                // кол-во минут с начала дня до конца рабочего дня
                $minEndDay = $schedule->end_work->diffInMinutes($day);
                // запланированые заявки на данный день
                $planning = $post->orderPlannings()->whereDate('start_date', $day)->whereDate('end_date', $day)->get();
                // получаем все временые метки с шагом, для данного поста, от начало раб. дня до конца
                $allTimes = [];
                for ($i = $minStartDay; $i < $minEndDay; $i = $i + $step) {
                    $allTimes[] = $i;
                }

                // здесь перебираем запланированае заявки (переданные с аа)
                foreach ($planning as $item){
                    /** @var $item AAOrderPlanning */
                    $start = $item->start_date->diffInMinutes($day);
                    $end = $item->end_date->diffInMinutes($day);

                    // здесь проходим по все временым метка, и записываем индексы тех меток,
                    // в диапазоне которых занято время, или метки должны совпадать или берем
                    // нижнюю метку для start_date и верхнюю для end_date данной заявки
                    $indexes = [];
                    foreach ($allTimes as $index => $stamp){
                        if($stamp == $start){
                            $indexes[] = $index;
                        } elseif ($stamp < $start && $stamp > ($start - $step)) {
                            $indexes[] = $index;
                        }

                        if($stamp == $end){
                            $indexes[] = $index;
                        } elseif ($stamp > $end && $stamp < ($end + $step)) {
                            $indexes[] = $index;
                        }
                    }

                    // здесь удаляем все занятые метки
                    for ($i = current($indexes); $i < last($indexes); $i++) {
                        unset($allTimes[$i]);
                    }
                    $allTimes = array_values($allTimes);
                }

                // получаем заявки, по данному посту на текущий день, возможна ситуация,
                // что время уже занято, но АА еще не потвердило его
                Order::query()
                    ->with(['additions'])
                    ->whereHas('additions', function(Builder $q) use ($day, $post) {
                        $q->whereDay('on_date', $day)
                            ->where('post_uuid', $post->uuid);
                    })->get()->each(function(Order $item) use ($day, &$allTimes) {
                        $minutes = $item->additions->on_date->diffInMinutes($day);
                        if (($key = array_search($minutes, $allTimes)) !== false) {
                            unset($allTimes[$key]);
                        }
                    });

                // записываем в массив все свободные метки по данному посту
                $tmp[$post->uuid] = array_values($allTimes);
            }
        }

        // получаем все временые метки с шагом, от начала дня до конца
        $allDayTimes = [];

        for ($i = 0; $i < ( 60 * 24 ); $i = $i + $step) {
            $allDayTimes[] = $i;
        }

        $result = [];
        foreach ($tmp as $postId => $datum){
            foreach ($datum as $d){
                if(in_array($d, $allDayTimes) && !array_key_exists($d, $result)){
                    $result[$d] = $postId;
                }
            }
        }
        ksort($result);

        // формируем результируюущие данные
        $finalResult = [];
        foreach ($result as $minutes => $id) {
            $finalResult[] = [
                'postUuid' => $id,
                'humanTime' => $day->addMinutes($minutes)->format('H:i'),
                'milliseconds' => $minutes * 60000,
            ];
        }
        OrderLogger::info('FREE TIME - OUTPUT', $finalResult);
        return $finalResult;
    }

    // old
//    public function getFreeTimes(array $data): array
//    {
//        /** @var $service Service */
//        $service = $this->serviceRepository->getByID($data['serviceId']);
//
//        if(!($service->isServiceParent() || $service->isBody())){
//            OrderFreeTimeException::serviceNotSupport();
//        }
//
//        /** @var $dealership Dealership */
//        $dealership = $this->dealershipRepository->getByID($data['dealershipId'], ['timeStep', 'departments.schedule']);
//
//        $step = ($model = $dealership->timeStep->where('service_id', $service->id)->first())
//            ? $model->step
//            : TimeStep::DEFAULT
//        ;
//
//        if($service->isServiceParent()){
//            $type = Department::TYPE_SERVICE;
//        }
//        if($service->isBody()){
//            $type = Department::TYPE_BODY;
//        }
//
//        if(null == $dealership->departments->where('type', $type)->first()->schedule){
//            OrderFreeTimeException::notHaveSchedule();
//        }
//
//        $schedule = $dealership->departments->where('type', $type)->first()->schedule;
//        $day = DateTime::fromMillisecondToDayOfWeek($data['date']);
//        $scheduleDay = $schedule->where('day', $day)->first();
//
//        if(null == $scheduleDay || null == $scheduleDay->from || null == $scheduleDay->to){
//            OrderFreeTimeException::notHaveSchedule();
//        }
//
////        $step = 1800000;
//        $time = [];
//        for($i = $scheduleDay->from; $i < $scheduleDay->to; $i += $step){
//            if(isset($data['human'])){
//                $time[] = DateTime::timeFromMillisecond($i);
//            } else {
//                $time[] = $i;
//            }
//        }
//        $date = DateTime::fromMillisecondToCarboneImutableDate($data['date']);
//
//        $pastTime = 0;
//        if($date->isCurrentDay()){
//            $pastTime = $data['date'] - (Carbon::today()->timestamp * 1000);
//        } else {
//            $data['date'] = $date->startOfDay()->timestamp * 1000;
//        }
//
//        foreach ($time as $key => $item){
//
//            if(isset($data['human'])){
//                $item *= 3600000;
//            }
//
//            $step = $step - 60000;
//            $from = DateTime::fromMillisecondToDate(($data['date'] + $item) - $step);
//            $to = DateTime::fromMillisecondToDate(($data['date'] + $item) + $step);
//
//            $exist = $this->orderRepository->existOrderByTime(
//                $dealership->id,
//                $service->id,
//                $from,
//                $to,
//                [Status::CREATED, Status::IN_PROCESS]
//            );
//
//            if($exist){
//                unset($time[$key]);
//            }
//
//            if($item < $pastTime){
//                unset($time[$key]);
//            }
//
//        }
//
//        return array_values($time);
//    }
}



