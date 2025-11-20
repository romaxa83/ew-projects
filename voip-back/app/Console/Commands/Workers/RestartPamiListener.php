<?php

namespace App\Console\Commands\Workers;

use App\PAMI\Message\Event\CoreShowChannelsCompleteEvent;
use App\PAMI\Service\SendActionService;
use Illuminate\Console\Command;

// т.к. слушатель жрет много памяти, данный воркер перезапускает его, запускается раз в час, идет запрос
// в ami, есть ли активные звонки, если нету перезапускается, если есть повторяет попытку через n кол-во сек
// если все попытки проваливаются, то в течение данного часа перезапуск не происходит
class RestartPamiListener extends Command
{
    protected $signature = 'workers:restart_pami_listener';

    protected $description = 'Перезапускает слушателя событий ami';

    protected $command;
    protected $try;
    protected $sleep;

    public function __construct()
    {
        $this->command = config('asterisk.ami_demon.restart_command');
        $this->try = config('asterisk.ami_demon.restart_try');
        $this->sleep = config('asterisk.ami_demon.restart_sleep');

        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            while ($this->try){
                if($this->existActiveChannel()){
                    logger_info("[pami-demon] RESTART pami listener [try = {$this->try}]");
                    $this->try--;
                    sleep($this->sleep);
                } else {
                    $this->restart();
                    logger_info("[pami-demon] RESTART pami listener success");
                    break;
                }
            }

            $time = microtime(true) - $start;
            $this->info($time);
            logger_info("[pami-demon] RESTART pami listener [time = {$time}]");
        } catch (\Exception $e){
            logger_info("[pami-demon] RESTART pami listener FAIL", [$e]);
            $this->error($e->getMessage(), []);
        }
    }

    protected function existActiveChannel(): bool
    {
        /** @var $sendService SendActionService */
        $sendService = resolve(SendActionService::class);
        $res = $sendService->CoreShowChannelsAction();

        $exist = false;

        if($res->isSuccess()){
            foreach ($res->getEvents() as $event){
                if($event instanceof CoreShowChannelsCompleteEvent){
                    if($event->getListItems() != '0'){
                        $exist = true;
                    }
                }
            }

            return $exist;
        } else {
            throw new \Exception('[pami-demon] RESPONSE core show channel return false');
        }
    }

    protected function restart()
    {
        if($cmd = $this->command){
            $output=null;
            $retval=null;

            exec($cmd, $output, $retval);

            logger_info("[pami-demon] RESTART pami listener EXEC",[
                'cmd' => $cmd,
                'output' => $output,
                'retval' => $retval,
            ]);
        } else {
            logger_info("[pami-demon] RESTART pami listener NOT EXEC");
        }
    }
}
