<?php

namespace App\Console\Commands\Helpers\PAMI;

use App\PAMI\Listener\DialListener;
use App\PAMI\Listener\HangupListener;
use App\PAMI\Listener\QueueStatusListener;
use App\PAMI\Listener\VarSetListener;
use App\Repositories\Employees\EmployeeRepository;
use Exception;
use Illuminate\Console\Command;
use App\PAMI\Client\Impl\ClientAMI;
use App\PAMI\Message\Action\QueueStatusAction;
use Illuminate\Support\Facades\Log;

class Listener extends Command
{
    protected $signature = 'pami:listener';
    protected $description = 'Запускает прослушивателя событий ami';

    public function __construct(
        protected ClientAMI $client,
        protected EmployeeRepository $employeeRepository
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            if(config('asterisk.ami_logger_enable')){
                $this->client->setLogger(Log::channel('ami'));
            }
            $this->client->open();

            $this->client->send(new QueueStatusAction());

            $this->client->registerEventListener(new QueueStatusListener());
            $this->client->registerEventListener(new VarSetListener());

            $this->client->registerEventListener(new HangupListener());
            $this->client->registerEventListener(new DialListener());

            if(config('asterisk.ami_logger_enable')){
                // смотрим какие события происходят
                $this->client->registerEventListener(
                    function (\App\PAMI\Message\Event\EventMessage $event){
                        $this->info($event->getName());
                    }
                );
            }

            (new QueueEntryHandler($this->client))->run();

            while (true){
                $this->client->process();

                usleep(1000);
            }

            $this->client->close();

        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            return self::FAILURE;
        }
    }
}



