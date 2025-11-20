<?php

namespace WezomCms\Core\Commands\Translations;

use WezomCms\Core\Models\Translation;

class DeleteUnusedCommand extends ScanCommand
{
    protected $signature = 'translations:delete-unused';

    protected $description = 'Delete translations without usage';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $translationKeys = $this->findProjectTranslationsKeys();

        $savedKeys = $this->storage->getAlreadySavedTranslatedKeys();

        $unused = [];
        foreach ($savedKeys as $key) {
            if (!in_array($key, $translationKeys)) {
                $unused[] = $key;
                $this->warn(" - Unused key: '{$key}'");
            }
        }

        if ($unused) {
            $this->warn('Found: ' . count($unused) . ' keys!');
            if ($this->confirm('Delete unused translations?')) {
                foreach ($unused as $key) {
                    $criteria = Translation::parseKey($key);

                    $this->storage->deleteByCriteria($criteria);
                }
                $this->info('Unused translations successfully deleted!');
            }
        } else {
            $this->info('Unused keys not found!');
        }

        $this->line('');
    }
}
