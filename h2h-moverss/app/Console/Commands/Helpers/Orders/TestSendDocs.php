<?php

namespace App\Console\Commands\Helpers\Orders;

use App\Models\Attachment;
use App\Services\Orders\OrderNotificationService;
use Illuminate\Console\Command;

class TestSendDocs extends Command
{
    protected $signature = 'helpers:send_docs';

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

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec()
    {
//        dd(config('mail.dev-subject'));

        // for local and test env
         $orderId = 140266;
         $attachId = 14793;
//        $orderId = $this->ask('Order ID');
//        $attachId = $this->ask('Attach ID');

        $service = resolve(OrderNotificationService::class);

        $attachment = Attachment::find($attachId);

        $service->sendDocs($orderId, $attachment, 'estimate');


    }
}