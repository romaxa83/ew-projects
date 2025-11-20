<?php

namespace App\Console\Commands\Workers;

use App\PAMI\Client\Impl\ClientAMI;
use App\PAMI\Message\Action\QueueStatusAction;
use App\PAMI\Message\Event\EventMessage;
use App\PAMI\Message\Event\QueueEntryEvent;
use App\PAMI\Message\Response\ResponseMessage;
use App\Repositories\Departments\DepartmentRepository;
use App\Services\ARI\ClientARI;
use App\Services\ARI\Commands\Info\InfoCommand;
use App\Services\ARI\Commands\Music\StopPlayingMusicCommand;
use App\Services\Musics\MusicService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class HoldMusic extends Command
{
    protected $signature = 'workers:hold_music';

    protected $description = 'За пол часа до окончания рабочего дня, убираем настройки';

    public function __construct(
        protected MusicService $service,
        protected ClientAMI $clientAMI,
        protected ClientARI $clientARI,
        protected DepartmentRepository $departmentRepository
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {


        try {
            $start = microtime(true);

            $res = $this->service->holdMusicToEndWorkDay();

//            if($res){
//                $this->getRingChannelFromAmi();
//            }

            $time = microtime(true) - $start;
            logger_info("[worker] HOLD MUSIC [{$time}]");
        } catch (\Exception $e){
            logger_info("[worker] HOLD MUSIC", [$e]);
            $this->error($e->getMessage(), []);
        }
    }

//    protected function getRingChannelFromAmi()
//    {
//        $departments = $this->departmentRepository->getDepartmentIdsAndName();
//
//// /channels/{channelId}/moh
////        $uri = 'PJSIP/kamailio-000000d3';
////        $channelId = 'asterisk-docker01-1696505751.859';
//        $channelId = 'PJSIP/kamailio-000000d3';
//
//
////        $res = resolve(InfoCommand::class)->exec();
//
//        try {
//
//            $this->clientAMI->open();
//            $this->clientAMI->setLogger(Log::channel('ari'));
//            /** @var $res ResponseMessage */
//            $resAmi = $this->clientAMI->send(new QueueStatusAction());
//
//            $this->clientAMI->close();
//
//            $channels = [];
//            foreach ($resAmi->getEvents() as $event) {
//                /** @var $event EventMessage */
//                if(
//                    $event instanceof QueueEntryEvent
////                  && array_key_exists($event->getQueue(), $departments)
//                ){
//                    dd($event);
//
//                    $this->info($event->getChannel());
//                    $this->info($event->getUniqueid());
////                    $channels[] = $event->getUniqueid();
////                    $channels[] = $event->getChannel();
//                }
//            }
//
////            if(!empty($channels)){
////                /** @var $command StopPlayingMusicCommand */
////                $command = resolve(StopPlayingMusicCommand::class);
////
////                foreach ($channels as $id){
////                    $res = $command
////                        ->channelId($id)
////                        ->exec();
////
////                }
////            }
//
//        } catch (\Throwable $e){
//            $this->error($e->getMessage());
//        }
//    }
}
