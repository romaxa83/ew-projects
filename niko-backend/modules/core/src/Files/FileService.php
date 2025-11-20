<?php

namespace WezomCms\Core\Files;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Traits\Model\FileAttachable;

class FileService
{
    public const FILE = 'file';

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var array
     */
    private $locales;
    /**
     * @var string
     */
    private $defaultDisk;

    /**
     * FileService constructor.
     */
    public function __construct()
    {
        $this->locales = array_keys(app('locales'));

        $this->defaultDisk = config('cms.core.files.storage');
    }

    /**
     * @param  Model|Translatable|FileAttachable  $model
     * @param  SymfonyUploadedFile|SymfonyUploadedFile[]|array|mixed $source
     * @param $field
     * @return bool
     */
    public function uploadFile(Model $model, $source, $field): bool
    {
        $setting = $this->extractSetting($model, $field);

        $directory = array_get($setting, 'directory');
        if (array_get($setting, 'multilingual', false)) {
            foreach ($this->locales as $locale) {
                if (is_array($source) && $item = array_get($source, $locale)) {
                    if ($item instanceof SymfonyUploadedFile && $item->getError() !== UPLOAD_ERR_OK) {
                        continue;
                    }

                    $this->storeOneFile($item, $directory, $setting, $model->translateOrNew($locale), $field);
                }
            }
        } else {
            if (!$source || ($source instanceof SymfonyUploadedFile && $source->getError() !== UPLOAD_ERR_OK)) {
                return false;
            }

            $this->storeOneFile($source, $directory, $setting, $model, $field);
        }

        return true;
    }

    /**
     * @param  Model|Translatable|FileAttachable  $model
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     */
    public function getFileUrl(Model $model, string $field = self::FILE, string $locale = null): ?string
    {
        if (!$this->fileExists($model, $field, $locale)) {
            return null;
        }

        $setting = $this->extractSetting($model, $field);

        $storedFileName = $this->getStoredFileName($model, $setting, $field, $locale);

        if ($storedFileName) {
            return $this->getStorage($setting)
                ->url($this->buildPath([array_get($setting, 'directory'), $storedFileName]));
        }

        return null;
    }

    /**
     * @param  Model|Translatable|FileAttachable  $model
     * @param  string  $field
     * @param  string|null  $locale
     * @return bool
     */
    public function fileExists(Model $model, string $field = self::FILE, string $locale = null): bool
    {
        $setting = $this->extractSetting($model, $field);

        $storedFileName = $this->getStoredFileName($model, $setting, $field, $locale);

        if ($storedFileName) {
            return $this->getStorage($setting)
                ->exists($this->buildPath([array_get($setting, 'directory'), $storedFileName]));
        }

        return false;
    }

    /**
     * @param  Model|Translatable|FileAttachable  $model
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     */
    public function getFileName(Model $model, string $field = self::FILE, string $locale = null): ?string
    {
        if (!$this->fileExists($model, $field, $locale)) {
            return null;
        }

        return $this->getStoredFileName(
            $model,
            $this->extractSetting($model, $field),
            $field,
            $locale
        );
    }

    /**
     * @param  Model|Translatable|FileAttachable  $model
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     */
    public function getOriginalFileName(Model $model, string $field = self::FILE, string $locale = null): ?string
    {
        if (!$this->fileExists($model, $field, $locale)) {
            return null;
        }

        $setting = $this->extractSetting($model, $field);

        if ($originalNameField = array_get($setting, 'original_name')) {
            if (array_get($setting, 'multilingual', false)) {
                return $model->translateOrNew($locale)->getAttribute($originalNameField);
            } else {
                return $model->getAttribute($originalNameField);
            }
        }

        return null;
    }

    /**
     * @param  Model|Translatable|FileAttachable  $model
     * @param  bool  $formatted
     * @param  string  $field
     * @param  string|null  $locale
     * @return mixed
     */
    public function getFileSize(
        Model $model,
        bool $formatted = true,
        string $field = self::FILE,
        string $locale = null
    ): ?string {
        if (!$this->fileExists($model, $field, $locale)) {
            return null;
        }

        $setting = $this->extractSetting($model, $field);

        $size = $this->getStorage($setting)
            ->size($this->buildPath([array_get($setting, 'directory'), $model->getAttribute($field)]));

        if (!$size) {
            return null;
        }

        return $formatted ? Helpers::bytesToHuman($size) : $size;
    }

    /**
     * @param  Model|Translatable|FileAttachable  $model
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     */
    public function getFileExtension(Model $model, string $field = self::FILE, string $locale = null): ?string
    {
        if (!$this->fileExists($model, $field, $locale)) {
            return null;
        }

        return pathinfo(
            $this->getStoredFileName($model, $this->extractSetting($model, $field), $field, $locale),
            PATHINFO_EXTENSION
        );
    }

    /**
     * @param  Model|Translatable|FileAttachable  $model
     * @param  string  $field
     * @param  array  $setting
     * @param  string|null  $locale
     * @param  bool  $save
     * @return bool
     */
    public function deleteFile(
        Model $model,
        string $field = self::FILE,
        array $setting = null,
        string $locale = null,
        bool $save = true
    ): bool {
        if (null === $setting) {
            $setting = $this->extractSetting($model, $field);
        }

        $directory = array_get($setting, 'directory');

        if (array_get($setting, 'multilingual', false)) {
            if (null !== $locale) {
                // delete concrete locale
                $fileName = $this->getStoredFileName($model, $setting, $field, $locale);

                $this->deleteConcreteFile($model->translateOrNew($locale), $setting, $directory, $field, $fileName);
            } else {
                // delete all locales
                foreach ($this->locales as $localLocale) {
                    $fileName = $this->getStoredFileName($model, $setting, $field, $localLocale);

                    $this->deleteConcreteFile(
                        $model->translateOrNew($localLocale),
                        $setting,
                        $directory,
                        $field,
                        $fileName
                    );
                }
            }
        } else {
            $this->deleteConcreteFile($model, $setting, $directory, $field, $model->getAttribute($field));
        }

        if ($save) {
            $model->save();
        }

        return true;
    }

    /**
     * @param  Model|Translatable|FileAttachable  $model
     * @param  bool  $save
     * @return bool
     */
    public function deleteAllFiles(Model $model, bool $save = true): bool
    {
        foreach (array_keys($model->fileSettings()) as $field) {
            $this->deleteFile($model, $field, null, null, $save);
        }

        return true;
    }

    /**
     * @param  Model|FileAttachable  $model
     * @param  string  $field
     * @return array
     */
    public function extractSetting(Model $model, string $field = self::FILE): array
    {
        $result = array_get($model->fileSettings(), $field);

        if (!is_array($result)) {
            $result = config()->get($result, []);
        }

        if (array_get($result, 'original_name') === true) {
            $result['original_name'] = $field . '_original_name';
        }

        return $result;
    }

    /**
     * @param  array  $setting
     * @return bool
     */
    public function isMultilingual(array $setting): bool
    {
        return array_get($setting, 'multilingual', false);
    }

    /**
     * @param  Model|Translatable|FileAttachable  $model
     * @param  array  $setting
     * @param  string  $field
     * @param  string|null  $locale
     * @return mixed
     */
    private function getStoredFileName(Model $model, array $setting, string $field = self::FILE, string $locale = null)
    {
        return array_get($setting, 'multilingual', false)
            ? $model->translateOrNew($locale)->getAttribute($field)
            : $model->getAttribute($field);
    }

    /**
     * @param  Model  $model
     * @param  array  $setting
     * @param $directory
     * @param  string  $field
     * @param  string|null  $fileName
     * @return Model
     */
    private function deleteConcreteFile(
        Model $model,
        array $setting,
        $directory,
        string $field,
        ?string $fileName = null
    ) {
        if ($fileName) {
            $storage = $this->getStorage($setting);

            $path = $this->buildPath([$directory, $fileName]);
            if ($storage->exists($path)) {
                $storage->delete($path);
            }
        }

        // Update model attribute.
        $model->setAttribute($field, null);

        if ($originalNameField = array_get($setting, 'original_name')) {
            $model->setAttribute($originalNameField, null);
        }

        return $model;
    }

    /**
     * @param  SymfonyUploadedFile  $file
     * @param $directory
     * @param  array  $setting
     * @param  Model  $model
     * @param $field
     */
    private function storeOneFile($file, $directory, array $setting, Model $model, $field)
    {
        $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();

        $storage = $this->getStorage($setting);
        $storage->makeDirectory($directory);
        $storage->putFileAs($directory, $file, $fileName);

        $model->withoutEvents(function () use ($model, $field, $fileName, $setting, $file) {
            $model->setAttribute($field, $fileName);

            if ($originalNameField = array_get($setting, 'original_name')) {
                $model->setAttribute($originalNameField, $file->getClientOriginalName());
            }

            $model->save();
        });
    }

    /**
     * @param  array  $setting
     * @return FilesystemAdapter
     */
    protected function getStorage(array $setting): FilesystemAdapter
    {
        return Storage::disk(array_get($setting, 'storage', $this->defaultDisk));
    }

    /**
     * @param  array  $parts
     * @return string
     */
    protected function buildPath(array $parts): string
    {
        return implode('/', array_filter($parts));
    }
}
