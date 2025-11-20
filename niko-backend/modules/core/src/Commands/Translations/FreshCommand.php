<?php

namespace WezomCms\Core\Commands\Translations;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use WezomCms\Core\Models\Translation;

class FreshCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'translations:fresh';

    protected $description = 'Delete all translations and search new keys and update translation storage';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        if ($this->confirmToProceed()) {
            Translation::truncate();

            $this->warn('Translation table truncated!');
            $this->line('');
            $this->warn('Scanning new keys & save to storage...');

            $this->callSilent('translations:scan');

            $this->line('');
            $this->info('All translations successfully stored!');
            $this->line('');
        }
    }
}
