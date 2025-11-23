<?php

namespace App\Console\Commands\Helpers;

use App\Models\User\Role;
use Illuminate\Console\Command;

class AddDataTorRole extends Command
{
    private $data = [
        'Foreman',
        'Helper',
        'Drivers',
        'Extra Helper',
        'Estimator',
    ];

    protected $signature = 'helpers:add_data_to_role';

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
        Role::query()
            ->whereIn('title', $this->data)
            ->update([
                'for_crew' => true
            ]);
    }
}