<?php

namespace WezomCms\Core\Commands\Translations;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use WezomCms\Core\Models\Translation;

class FreshCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:fresh';

    /**
     * The console command description.
     *
     * @var string|null
     */
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
