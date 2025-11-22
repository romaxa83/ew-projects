<?php


namespace App\Console\Commands;


use App\Models\History\History;
use Illuminate\Console\Command;

class ClearUsersHistory extends Command
{
    protected $signature = 'clear-users-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear users history (update and create messages)';

    public function handle(): int
    {
        History::query()
            ->where('model_type', 'App\Models\Users\User')
            ->whereIn('message', ['history.user_created', 'history.user_updated'])
            ->delete();

        $this->info('Done.');

        return self::SUCCESS;
    }
}
