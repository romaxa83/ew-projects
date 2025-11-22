<?php

namespace App\Console\Commands\Payments;

use App\Services\Payment\PayPalService;
use Illuminate\Console\Command;

class CheckPayPalSetting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paypal:check-setting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking the correctness of the webhook settings.';

    /**
     * Create a new command instance.
     *
     * @param PayPalService $payPalService
     */
    public function __construct(private PayPalService $payPalService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Process start...');

        return $this->payPalService->checkIntegration($this) ? Command::SUCCESS : Command::FAILURE;
    }
}
