<?php

namespace App\Console\Commands\Init;

use App\Repositories\Admin\AdminRepository;
use Cache;
use Illuminate\Console\Command;

class CacheCheck extends Command
{
    protected $signature = 'am:check-cache';

    protected $description = 'Check cache';

    /**
     * @param AdminRepository $adminRepository
     */
    public function handle(AdminRepository $adminRepository)
    {
        Cache::store('redis')->put('key', 'val', 600);

        $val = Cache::get('key');

        if($val == 'val'){
            $this->info("[✔] - check");

            return;
        }

        $this->warn("something wrong");
    }
}

