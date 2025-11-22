<?php

namespace App\Console\Commands\Workers;

use App\Models\Saas\Company\Company;
use App\Services\SendPulse\Commands\RequestCommand;
use App\Services\SendPulse\Commands\SendAddressCommand;
use Illuminate\Console\Command;

class SendCompanyToAddressbook extends Command
{
    protected $signature = 'sendpulse:send_company';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            logger_info("[helper] ".__CLASS__." [time = {$time}]");
            $this->info("[helper] ".__CLASS__." [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info("[helper] ".__CLASS__." FAIL", [
                'msg' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function exec(): void
    {
        $companies = Company::query()->where('send_to_sendpulse', '=', false)->get();

        if($companies->isNotEmpty()){
            /** @var $command RequestCommand */
            $command = resolve(SendAddressCommand::class);
            $res = $command->handler(['companies' => $companies]);

            if(array_key_exists('result', $res) && $res['result']){
                $companies->map(fn(Company $i) => $i->update(['send_to_sendpulse' => true]));
            }
        }
    }
}

