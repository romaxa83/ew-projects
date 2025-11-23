<?php

namespace App\Console\Commands\Import;

use App\Http\Controllers\Import\AuthorizePaymentController;
use Illuminate\Console\Command;

class AuthorizePaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:import-authorize-payments {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import authorize';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(AuthorizePaymentController $controller)
    {
        return $controller->import($this->argument('id'));
    }
}
