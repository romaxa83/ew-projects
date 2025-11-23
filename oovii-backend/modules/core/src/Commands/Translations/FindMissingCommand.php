<?php

namespace WezomCms\Core\Commands\Translations;

class FindMissingCommand extends ScanCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:find-missing';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Search new keys without saved translations';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        if ($newKeys = $this->getNewTranslationsKeys()) {
            $this->info('Found: ' . count($newKeys) . ' keys!');
        } else {
            $this->info('New keys not found!');
        }
        $this->line('');
    }
}
