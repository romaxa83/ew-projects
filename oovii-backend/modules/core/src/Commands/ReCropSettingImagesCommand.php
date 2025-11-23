<?php

namespace WezomCms\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\FilesystemAdapter;
use Image;
use Storage;
use WezomCms\Core\Image\ImageHandler;
use WezomCms\Core\Image\ImageService;
use WezomCms\Core\Models\Setting;
use WezomCms\Core\Models\SettingTranslation;
use WezomCms\Core\Settings\Fields\AbstractField;

class ReCropSettingImagesCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:re-crop-settings
                            {--path= : A dot separated key with up to three parts, e.g., "module.group.key"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-crop settings images from original file for each size';

    /**
     * @var ImageService
     */
    protected $imageService;

    /**
     * @var array
     */
    protected $locales;

    /**
     * ReCropImagesCommand constructor.
     * @param  ImageService  $imageService
     */
    public function __construct(ImageService $imageService)
    {
        parent::__construct();

        $this->imageService = $imageService;
        $this->locales = array_keys(app('locales'));
    }

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $rows = $this->getSettings();

        $this->output->progressStart($rows->count());
        foreach ($rows as $setting) {
            $this->reCropImages($setting);

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    /**
     * @param  Setting  $model
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Throwable
     */
    protected function reCropImages(Setting $model): void
    {
        $settings = $model->image_settings;

        $directory = data_get($settings, 'directory');
        if (!$sizes = data_get($settings, 'sizes', [])) {
            return;
        }
        $sizes = json_decode(json_encode($sizes), true);

        $storage = $this->getStorage($settings);
        $originalStorage = $this->getOriginalStorage($settings);

        $previousFilename = null;
        foreach ($this->locales as $locale) {
            /** @var SettingTranslation $translation */
            $translation = $model->translateOrNew($locale);
            $path = $this->buildPath([Setting::STORAGE_DIR, $directory, ImageService::ORIGINAL, $translation->value]);

            if (!$translation->value || $translation->value === $previousFilename || $originalStorage->missing($path)) {
                continue;
            }
            $previousFilename = $translation->value;

            $originalFileContent = $originalStorage->get($path);

            foreach ($sizes as $size => $sizeSettings) {
                $targetFile = $this->buildPath([Setting::STORAGE_DIR, $directory, $size, $translation->value]);

                $storage->delete($targetFile, $targetFile . '.webp');

                ImageHandler::make(Image::make($originalFileContent), $storage)
                    ->modify($sizeSettings)
                    ->save($targetFile);
            }
        }
    }

    /**
     * @param  array  $parts
     * @return string
     */
    protected function buildPath(array $parts): string
    {
        return implode('/', array_filter($parts));
    }

    /**
     * @param  array|object  $setting
     * @return FilesystemAdapter
     */
    protected function getStorage($setting): FilesystemAdapter
    {
        return Storage::disk(data_get($setting, 'storage', config('cms.core.image.storage')));
    }

    /**
     * @param  array|object  $setting
     * @return FilesystemAdapter
     */
    protected function getOriginalStorage($setting): FilesystemAdapter
    {
        return Storage::disk(data_get($setting, 'original_storage', config('cms.core.image.original_storage')));
    }

    /**
     * @return Builder[]|Collection|Setting[]
     */
    protected function getSettings(): Collection
    {
        /** @var Builder|Setting $query */
        $query = Setting::whereType(AbstractField::TYPE_IMAGE)
            ->whereHas('translations', function ($query) {
                $query->whereNotNull('value');
            });

        $constraints = explode('.', (string) $this->option('path'));
        if (!empty($constraints[0])) {
            $query->whereModule($constraints[0]);
        }
        if (!empty($constraints[1])) {
            $query->whereGroup($constraints[1]);
        }
        if (!empty($constraints[2])) {
            $query->where('key', $constraints[2]);
        }

        return $query->get();
    }
}
