<?php

namespace WezomCms\Core\Commands\Translations;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Lang;
use WezomCms\Core\Contracts\TranslationStorageInterface;
use WezomCms\Core\Contracts\TranslatorDriverInterface;
use WezomCms\Core\Drivers\DetectSourceLocaleException;
use WezomCms\Core\Drivers\GoogleTranslator;
use WezomCms\Core\Models\Translation;

class TranslateCommand extends Command
{
    protected $signature = 'translations:translate
                            {--ns= : Namespace}
                            {--side= : Side name "admin" or "site"}
                            {--locale= : Locale key}
                            {--source-locale= : Source locale}
                            {--driver= : Translator driver "google" etc.}
                            {--driver-args= : Arguments passed for driver __construct method}';

    protected $description = 'Translate stored keywords via translator driver and update translation storage';

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
     * @throws \Exception
     */
    public function handle()
    {
        $translator = $this->makeTranslator();

        $locale = $this->option('locale');
        $sourceLocale = $this->option('source-locale');

        /** @var Collection|Translation[] $saved */
        $saved = $this->storage->getAlreadySaved($this->option('ns'), $this->option('side'), $locale, false);

        if ($saved->isEmpty()) {
            $this->warn('All keywords translated!');
        } else {
            foreach ($saved as $row) {
                $defaultKey = sprintf('%s::default.%s.%s', $row->namespace, $row->side, $row->key);

                if (!Lang::has($defaultKey, $row->locale)) {
                    $oldText = $row->text;

                    try {
                        $text = $translator->translate($oldText, $row->locale, $sourceLocale);
                    } catch (DetectSourceLocaleException $e) {
                        $this->error(sprintf('Can`t detect source locale for "%s" key', $oldText));
                        continue;
                    }

                    if ($text) {
                        if ($text == $oldText) {
                            $this->warn(sprintf('Not translated "%s"', $oldText));
                            $row->translated = true;
                            $row->save();
                        } else {
                            $row->text = $text;
                            $row->translated = true;
                            $row->save();

                            $this->info(sprintf('Translated "%s" -> "%s"', $oldText, $text));
                        }
                    }
                }
            }

            \Cache::forget(TranslationStorageInterface::CACHE_KEY);

            $this->info('Keywords successfully translated!');
        }
    }

    /**
     * @return TranslatorDriverInterface
     * @throws \Exception
     */
    private function makeTranslator(): TranslatorDriverInterface
    {
        switch ($this->option('driver')) {
            case 'google':
                $keyFilePath = $this->option('driver-args');
                $parts = explode(':', $keyFilePath);
                if (count($parts) === 2) {
                    $keyFilePath = $parts[0]($parts[1]);
                } else {
                    $keyFilePath = $parts[0];
                }

                return new GoogleTranslator($keyFilePath);
                break;
            default:
                throw new \Exception('Driver name is required');
            break;
        }
    }
}
