<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramDev;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class DuplicateUser extends Command
{
    protected $signature = 'jd:duplicate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Duplicate user';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $models = \DB::table('users')
            ->selectRaw('SELECT login, COUNT(*)')
            ->groupBy('login')->havingRaw('COUNT(*) > 1')
            ->get()
        ;

        dd($models);
    }
}
