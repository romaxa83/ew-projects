<?php

namespace WezomCms\Core\Commands\Translations;

use Illuminate\Console\Command;
use Lang;
use WezomCms\Core\Contracts\TranslationStorageInterface;

class MissingDefaultCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:missing-default
                            {--ns= : Namespace}
                            {--side= : Side name "admin" or "site"}
                            {--locale= : Locale key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show missing default keys';
    /**
     * @var TranslationStorageInterface
     */
    private $storage;

    /**
     * Create a new command instance.
     *
     * @param  TranslationStorageInterface  $storage
     */
    public function __construct(TranslationStorageInterface $storage)
    {
        parent::__construct();

        $this->storage = $storage;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $locale = $this->option('locale');
        $saved = $this->storage->getAlreadySaved($this->option('ns'), $this->option('side'), $locale);

        foreach ($saved as $row) {
            $defaultKey = sprintf('%s::default.%s.%s', $row->namespace, $row->side, $row->key);

            if (!Lang::has($defaultKey, $row->locale)) {
                $this->warn("- missing default for (locale: {$row->locale}): {$defaultKey}");
            }
        }

        $this->info('Done!');
    }
}
