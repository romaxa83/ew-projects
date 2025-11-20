<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use WezomCms\Firebase\UseCase\CallPushEvent;
use WezomCms\Services\Repositories\ServiceGroupRepository;
use WezomCms\Services\Types\ServiceType;
use WezomCms\ServicesOrders\Repositories\OrderRepository;
use WezomCms\TelegramBot\Telegram;

class TestCron extends Command
{
    protected $signature = 'niko:test-cron';

    protected $description = 'crone';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $time = now();

        Telegram::event('Time - ' . $time);
    }
}
