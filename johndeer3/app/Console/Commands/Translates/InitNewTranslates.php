<?php

namespace App\Console\Commands\Translates;

use Illuminate\Console\Command;

class InitNewTranslates extends Command
{
    protected $signature = 'jd:import-translates-init';

    protected $description = 'Run all command for new translation';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
//        $this->call('jd:upload-translates');
//        $this->call('jd:import-feature-translates');
//        $this->call('jd:import-translates');
//        $this->call('jd:copy-translates');
//        $this->call('jd:export-translates');


//        $this->call('jd:upload-translates');
//        $this->call('jd:copy-translates');
    }
}
