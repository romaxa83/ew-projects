<?php

namespace App\Console\Commands\ERP;

use App\Http\Controllers\API\SiteImportController;
use Illuminate\Console\Command;

class ImportSiteOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:import-orders {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import orders from site';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(SiteImportController $importController)
    {
        $site = $this->argument('name');

        if (!config("app.site_import.$site.mf_public")) {
            echo 'No config in env';
            return;
        }

        $importController->importOrders($site);
    }
}
