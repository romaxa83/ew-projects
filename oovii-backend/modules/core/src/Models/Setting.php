<?php

namespace WezomCms\Core\Models;

use Eloquent;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Image;
use Storage;
use Throwable;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Image\ImageHandler;
use WezomCms\Core\Image\ImageService;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\WithoutWebPWrapper;

/**
 * \WezomCms\Core\Models\Setting
 *
 * @property int $id
 * @property string $module
 * @property string $group
 * @property string $key
 * @property string|null $type
 * @property object|null $image_settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|SettingTranslation[] $translations
 * @method static Builder|Setting listsTranslations($translationField)
 * @method static Builder|Setting newModelQuery()
 * @method static Builder|Setting newQuery()
 * @method static Builder|Setting notTranslatedIn($locale = null)
 * @method static Builder|Setting orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Setting orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Setting orderByTranslation($key, $sortmethod = 'asc')
 * @method static Builder|Setting query()
 * @method static Builder|Setting translated()
 * @method static Builder|Setting translatedIn($locale = null)
 * @method static Builder|Setting whereCreatedAt($value)
 * @method static Builder|Setting whereGroup($value)
 * @method static Builder|Setting whereId($value)
 * @method static Builder|Setting whereImageSettings($value)
 * @method static Builder|Setting whereKey($value)
 * @method static Builder|Setting whereModule($value)
 * @method static Builder|Setting whereTranslation($key, $value, $locale = null)
 * @method static Builder|Setting whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Setting whereType($value)
 * @method static Builder|Setting whereUpdatedAt($value)
 * @method static Builder|Setting withTranslation()
 * @mixin Eloquent
 * @mixin SettingTranslation
 */
class Setting extends Model
{
    use Translatable;
    use HasFactory;

    // Directory where files / images will be downloaded.
    public const STORAGE_DIR = 'settings';

    public const TABLE = 'settings';

    protected $table = self::TABLE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['module', 'group', 'key', 'image_settings', 'type'];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = ['value'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['image_settings' => 'object'];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = ['image_settings' => '{}'];

    /**
     * @param Request $request
     * @param AbstractField $field
     * @param array $locales
     * @throws Throwable
     */
    public function updateValue(Request $request, AbstractField $field, $locales)
    {
        if ($field->isAttachment()) {
            if ($field->isMultilingual()) {
                $this->uploadMultilingualAttachment($request, $field, $locales);
            } else {
                $this->uploadAttachment($request, $field, $locales);
            }
        } else {
            foreach ($locales as $locale => $language) {
                $inputName = $field->getRenderSettings()->getTab()->getKey() . '-' . $field->getInputName($locale);
                $this->translateOrNew($locale)->value = $request->input(Helpers::convertFieldToDot($inputName));
            }
        }

        $this->type = $field->getType();

        $this->save();
    }

    /**
     * @param AbstractField $field
     * @param array $locales
     * @param Request $request
     * @throws Throwable
     */
    protected function uploadMultilingualAttachment(Request $request, AbstractField $field, array $locales): void
    {
        $storage = $this->getStorage($field->getType());
        foreach ($locales as $locale => $language) {
            if ($this->fileExists($locale)) {
                continue;
            }

            $fileName = $field->getGroup() . '-' . Helpers::convertFieldToDot($field->getInputName($locale));
            if ($field->getType() === AbstractField::TYPE_IMAGE) {
                $fileName = $locale . '.' . $fileName;
            }
            $file = $request->file($fileName);
            if (null === $file || $file->getError() !== UPLOAD_ERR_OK) {
                continue;
            }

            if ($field->getType() === AbstractField::TYPE_IMAGE) {
                $storeName = $this->saveImage($file, $field);
            } else {
                $storeName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $storage->putFileAs(self::STORAGE_DIR, $file, $storeName);
            }

            if ($storeName) {
                $value = pathinfo($storeName, PATHINFO_BASENAME);
                $this->translateOrNew($locale)->value = $value;
            }
        }
    }

    /**
     * @param string|null $type
     * @return Filesystem|null
     */
    protected function getStorage(?string $type = null): ?Filesystem
    {
        if ($type === null) {
            $type = $this->type;
        }

        switch ($type) {
            case AbstractField::TYPE_IMAGE:
                return $this->imageStorage();
            case AbstractField::TYPE_FILE:
                return $this->fileStorage();
            default:
                return null;
        }
    }

    /**
     * @param array|object|null $setting
     * @return Filesystem
     */
    protected function imageStorage($setting = null): Filesystem
    {
        if ($setting === null) {
            $setting = $this->image_settings;
        }

        return Storage::disk(data_get($setting, 'storage', config('cms.core.image.storage')));
    }

    /**
     * @param array|object|null $setting
     * @return Filesystem
     */
    protected function fileStorage($setting = null): Filesystem
    {
        if ($setting === null) {
            $setting = $this->image_settings;
        }

        return Storage::disk(data_get($setting, 'storage', config('cms.core.files.storage')));
    }

    /**
     * @param string|null $locale
     * @param null $size
     * @return bool
     */
    public function fileExists(string $locale = null, $size = null): bool
    {
        $fileName = null === $locale ? $this->value : $this->translateOrNew($locale)->value;

        if ($fileName === null) {
            return false;
        }

        $settings = $this->image_settings;

        if ($sizes = data_get($settings, 'sizes')) {
            if (null === $size) {
                $size = data_get($settings, 'default');
            }

            return $this->getStorageBySize($size)->exists(
                $this->buildPath([Setting::STORAGE_DIR, data_get($settings, 'directory'), $size, $fileName])
            );
        }

        return $this->getStorage()->exists(
            $this->buildPath([Setting::STORAGE_DIR, data_get($settings, 'directory'), $fileName])
        );
    }

    /**
     * @param string|null $size
     * @param array|null $setting
     * @return Filesystem
     */
    protected function getStorageBySize(?string $size, array $setting = null): Filesystem
    {
        return $size === ImageService::ORIGINAL ? $this->imageOriginalStorage($setting) : $this->imageStorage($setting);
    }

    /**
     * @param array|object|null $setting
     * @return Filesystem
     */
    protected function imageOriginalStorage($setting = null): Filesystem
    {
        if ($setting === null) {
            $setting = $this->image_settings;
        }

        return Storage::disk(data_get($setting, 'original_storage', config('cms.core.image.original_storage')));
    }

    /**
     * @param array $parts
     * @return string
     */
    protected function buildPath(array $parts): string
    {
        return implode('/', array_filter($parts));
    }

    /**
     * @param UploadedFile|UploadedFile[]|array|null $file
     * @param AbstractField|\WezomCms\Core\Settings\Fields\Image $field
     * @return string
     * @throws Throwable
     */
    private function saveImage($file, AbstractField $field)
    {
        $setting = $field->extractSettings();

        $clientOriginalExtension = $file->getClientOriginalExtension();

        $fileName = Str::random(40) . '.' . $clientOriginalExtension;

        $path = $this->buildPath([self::STORAGE_DIR, data_get($setting, 'directory')]);

        $storage = $this->imageStorage($setting);

        $dontModify = in_array($clientOriginalExtension, ['svg', 'gif']);
        if ($sizes = data_get($setting, 'sizes')) {
            // Image storage for each size.
            foreach ($sizes as $size => $settings) {
                $destinationDir = "{$path}/{$size}";

                $storage->makeDirectory($destinationDir);

                if ($dontModify) {
                    $storage->putFileAs($destinationDir, $file, $fileName);
                } else {
                    ImageHandler::make(Image::make($file), $storage)
                        ->modify($settings)
                        ->save("{$destinationDir}/{$fileName}");
                }
            }

            if (!$dontModify) {
                $this->imageOriginalStorage($setting)->putFileAs("{$path}/original", $file, $fileName);
            }
        } else {
            $storage->makeDirectory($path);
            $storage->putFileAs($path, $file, $fileName);
        }

        return $fileName;
    }

    /**
     * @param AbstractField $field
     * @param Request $request
     * @param array $locales
     * @throws Throwable
     */
    protected function uploadAttachment(Request $request, AbstractField $field, array $locales): void
    {
        if ($this->fileExists()) {
            return;
        }

        $fileName = $field->getGroup() . '-' . $field->getInputName();
        $file = $request->file($fileName);
        if (null === $file || $file->getError() !== UPLOAD_ERR_OK) {
            return;
        }

        if ($field->getType() === AbstractField::TYPE_IMAGE) {
            $storeName = $this->saveImage($file, $field);
        } else {
            $storeName = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $this->getStorage($field->getType())->putFileAs(self::STORAGE_DIR, $file, $storeName);
        }

        if ($storeName) {
            $value = pathinfo($storeName, PATHINFO_BASENAME);

            foreach ($locales as $locale => $language) {
                $this->translateOrNew($locale)->value = $value;
            }
        }
    }

    /**
     * @param string|null $locale
     * @return bool
     */
    public function deleteFile(string $locale = null)
    {
        $valueRow = $locale !== null ? $this->translate($locale) : $this;

        if ($valueRow->value === null) {
            return true;
        }

        $settings = $this->image_settings;
        $storage = $this->getStorage();

        $directory = data_get($settings, 'directory');
        if (($sizes = data_get($settings, 'sizes')) !== null) {
            foreach ($sizes as $size => $settings) {
                $path = $this->buildPath([Setting::STORAGE_DIR, $directory, $size, $valueRow->value]);
                $storage->delete([$path, $path . '.webp']);
            }
            $storage->delete(
                $this->buildPath([Setting::STORAGE_DIR, $directory, ImageService::ORIGINAL, $valueRow->value])
            );
        } else {
            $storage->delete($this->buildPath([Setting::STORAGE_DIR, $directory, $valueRow->value]));
        }

        $valueRow->value = null;

        return $this->save();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getFileUrl(string $locale = null): ?string
    {
        $fileName = null === $locale ? $this->value : $this->translateOrNew($locale)->value;

        if ($fileName === null) {
            return false;
        }

        $storage = $this->fileStorage();

        $path = $this->buildPath([static::STORAGE_DIR, $fileName]);

        return $storage->exists($path) ? $storage->url($path) : null;
    }

    /**
     * @param bool $formatted
     * @param string|null $locale
     * @param null $size
     * @return mixed
     */
    public function getFileSize(bool $formatted = true, string $locale = null, $size = null)
    {
        if (!$this->fileExists($locale)) {
            return null;
        }

        $fileName = null === $locale ? $this->value : $this->translateOrNew($locale)->value;

        $settings = $this->image_settings;
        if (($sizes = data_get($settings, 'sizes')) !== null) {
            $size = $size !== null ? $size : data_get($settings, 'default');

            $path = $this->buildPath([Setting::STORAGE_DIR, data_get($settings, 'directory'), $size, $fileName]);
            $storage = $this->getStorageBySize($size);
        } else {
            $path = $this->buildPath([static::STORAGE_DIR, $fileName]);
            $storage = $this->getStorage();
        }

        $fileSize = $storage->size($path);

        if (!$fileSize) {
            return null;
        }

        return $formatted ? Helpers::bytesToHuman($fileSize) : $fileSize;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getFileExtension(string $locale = null): ?string
    {
        if (!$this->fileExists($locale)) {
            return null;
        }

        $fileName = null === $locale ? $this->value : $this->translateOrNew($locale)->value;

        return pathinfo($fileName, PATHINFO_EXTENSION);
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getFileName(string $locale = null): ?string
    {
        if ($this->fileExists($locale)) {
            return null === $locale ? $this->value : $this->translateOrNew($locale)->value;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isSvg(): bool
    {
        return mb_strtolower($this->getImageExtension()) === 'svg';
    }

    /**
     * @return string
     */
    public function getImageExtension(): string
    {
        return pathinfo($this->value, PATHINFO_EXTENSION);
    }

    /**
     * @param string|null $size
     * @param string|null $field
     * @return null|string
     */
    public function getSvgContent(string $size = null, string $field = null)
    {
        $fileName = $this->value;
        if (!$fileName) {
            return null;
        }

        $setting = $this->image_settings;

        if (null === $size) {
            $size = data_get($setting, 'default');
        }

        if (!$setting) {
            $size = null;
        }

        try {
            return $this->imageStorage()
                ->get($this->buildPath([static::STORAGE_DIR, data_get($setting, 'directory'), $size, $fileName]));
        } catch (FileNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param string|null $size
     * @param string|null $field
     * @param string|null $locale
     * @return mixed
     * @throws Throwable
     */
    public function getExistImageUrl(string $size = null, string $field = null, string $locale = null)
    {
        return $this->imageExists($size, $field, $locale) ? $this->getImageUrl($size, $field, $locale) : null;
    }

    /**
     * @param string|null $size
     * @param string|null $field
     * @param string|null $locale
     * @return bool
     */
    public function imageExists(string $size = null, string $field = null, string $locale = null)
    {
        $setting = $this->image_settings;

        $storedFileName = $this->getStoredFileName($setting, $locale);
        if (!$storedFileName) {
            return false;
        }

        if (null === $size) {
            $size = data_get($setting, 'default');
        }

        if (!$setting) {
            $size = null;
        }

        return $this->getStorageBySize($size)
            ->exists($this->buildPath([static::STORAGE_DIR, data_get($setting, 'directory'), $size, $storedFileName]));
    }

    /**
     * @param array|object $setting
     * @param string|null $locale
     * @return mixed
     */
    private function getStoredFileName($setting, string $locale = null)
    {
        return data_get($setting, 'multilingual', false)
            ? $this->translateOrNew($locale)->getAttribute('value')
            : $this->getAttribute('value');
    }

    /**
     * @param string|null $size
     * @param string|null $field
     * @param string|null $locale
     * @return string|null
     * @throws Throwable
     */
    public function getImageUrl(string $size = null, string $field = null, string $locale = null)
    {
        $setting = $this->image_settings;

        if (null === $size) {
            $size = data_get($setting, 'default');
        }
        if (!$setting) {
            $size = null;
        }

        $path = $this->buildPath(
            [
                static::STORAGE_DIR,
                data_get($setting, 'directory'),
                $size,
                $this->getStoredFileName($setting, $locale),
            ]
        );

        if (ImageService::ORIGINAL === $size) {
            return $this->imageOriginalStorage()->url($path);
        }

        $storage = $this->imageStorage();

        if (!$this->imageExists($size, $field, $locale)) {
            $sizeSetting = data_get($setting, 'sizes.' . $size, []);

            // Make placeholder
            $path = ImageHandler::noImage(
                data_get($sizeSetting, 'width'),
                data_get($sizeSetting, 'height'),
                data_get($setting, 'placeholder'),
                $storage
            );
        }

        $pathWebp = $path . '.webp';
        if (ImageService::webPSupport() && $storage->exists($pathWebp)) {
            return $storage->url($pathWebp . '?v=' . $storage->lastModified($pathWebp));
        }

        return $storage->url($path . '?v=' . $storage->lastModified($path));
    }

    /**
     * @param string $field
     * @return array|null
     */
    public function getRecommendUploadImageSize(string $field = 'image'): ?array
    {
        $setting = $this->image_settings;

        if (!$setting) {
            return null;
        }

        $sizes = data_get($setting, 'sizes', []);

        if (!$sizes) {
            return null;
        }

        // Transform object to array for correct array_column function work.
        $sizes = json_decode(json_encode($sizes), true);

        $width = array_column($sizes, 'width');
        $height = array_column($sizes, 'height');

        if (!$width || !$height) {
            return null;
        }

        return [
            'width' => max($width),
            'height' => max($height),
        ];
    }

    /**
     * @return WithoutWebPWrapper
     */
    public function withoutWebp(): WithoutWebPWrapper
    {
        return new WithoutWebPWrapper($this);
    }
}
