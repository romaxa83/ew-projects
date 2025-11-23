<?php

namespace App\Console\Commands\Import;

use App\Http\Controllers\Mailbox\Gmail\GMailController;
use App\Models\Mailbox\Gmail\Account;
use Illuminate\Console\Command;

class GmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:import-gmail {mode} {accountId?} {fromHistoryId?} {limit=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import mail from Gmail';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(GMailController $controller)
    {
        $mode = $this->argument('mode');

        if ($mode === 'customSync') {
            if (!$this->argument('accountId') || !$this->argument('fromHistoryId')) {
                $this->info('Usage: php artisan site:import-gmail customSync accountId fromHistoryId limit');
                return;
            }

            $account = Account::findOrFail($this->argument('accountId'));
            [
                'messages' => $messages,
                'errors' => $errors,
                'lastHistoryId' => $lastHistoryId
            ] = $controller->getMsgByHistoryId($account, $this->argument('fromHistoryId'), $this->argument('limit'));

            $this->info('Finished');

            $this->line('Messages: '.count($messages));
            $this->line('Errors: '.count($errors));
            $this->line('lastHistoryId: '.$lastHistoryId);
        } else {
            $controller->cronSyncMail();
        }
    }

}
