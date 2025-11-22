<?php

namespace App\Console\Commands;

use App\Models\Alerts\Alert;
use Illuminate\Console\Command;

class DeleteOldAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old user notifications';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $timestamp = now()
            ->subDays(config('alerts.delete_after_days'));

        Alert::query()
            ->withoutGlobalScopes()
            ->where('created_at', '<', $timestamp->format('Y-m-d H:i:s'))
            ->delete();

        return 0;
    }
}
