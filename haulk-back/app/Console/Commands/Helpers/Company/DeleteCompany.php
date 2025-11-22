<?php

namespace App\Console\Commands\Helpers\Company;

use App\Models\Saas\Company\Company;
use App\Services\Orders\OrderService;
use App\Services\Saas\Companies\CompanyService;
use Illuminate\Console\Command;

class DeleteCompany extends Command
{
    protected $signature = 'helper:delete-company';

    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $id = $this->ask('Enter Company id');
        try {
            $start = microtime(true);

            $this->exec($id);

            $time = microtime(true) - $start;

            $this->info(sprintf("[helper] %s [time = %s]", __CLASS__ , $time));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info('[helper] FILL PAYMENT FOR MILE', [
                'msg' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function exec($id): void
    {
        $model = Company::find($id);
        if(!$model){
            throw new \Exception("Model not found [$id]");
        }

        /** @var $service CompanyService */
        $service = resolve(CompanyService::class);

        $service->delete($model);
    }
}
