<?php

namespace WezomCms\Core\Models;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Image;
use Storage;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Image\ImageHandler;
use WezomCms\Core\Image\ImageService;
use WezomCms\Core\Settings\Fields\AbstractField;

/**
 * \WezomCms\Core\Models\Setting
 *
 * @property int $id
 * @property string $module
 * @property string $group
 * @property string $key
 * @property string|null $type
 * @property array|null $image_settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Core\Models\SettingTranslation[] $translations
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting listsTranslations($translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting notTranslatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting orWhereTranslation($key, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting orWhereTranslationLike($key, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting orderByTranslation($key, $sortmethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting translated()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting translatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting whereImageSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting whereTranslation($key, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting whereTranslationLike($key, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Setting withTranslation()
 * @mixin \Eloquent
 * @mixin SettingTranslation
 */
class Setting extends Model
{
    use Translatable;

    // Directory where files / images will be downloaded.
    public const STORAGE_DIR = 'settings';

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
    protected $casts = ['image_settings' => 'array'];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = ['image_settings' => '{}'];

    /**
     * @param  Request  $request
     * @param  AbstractField  $field
     * @param  array  $locales
     */
    public function updateValue(Request $request, AbstractField $field, $locales)
    {
        if ($field->isAttachment()) {
            $storage = $this->getStorage($field->getType());

            if ($field->isMultilingual()) {
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
            } else {
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
                    $storage->putFileAs(self::STORAGE_DIR, $file, $storeName);
                }

                if ($storeName) {
                    $value = pathinfo($storeName, PATHINFO_BASENAME);

                    foreach ($locales as $locale => $language) {
                        $this->translateOrNew($locale)->value = $value;
                    }
                }
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
     * @param  string|null  $locale
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

        $directory = array_get($settings, 'directory');
        if (($sizes = array_get($settings, 'sizes')) !== null) {
            foreach ($sizes as $size => $settings) {
                $storage->delete(
                    implode('/', array_filter([Setting::STORAGE_DIR, $directory, $size, $valueRow->value]))
                );
            }
        } else {
            $storage->delete(implode('/', array_filter([Setting::STORAGE_DIR, $directory, $valueRow->value])));
        }

        $valueRow->value = null;

        return $this->save();
    }

    /**
     * @param  string|null  $locale
     * @param  null  $size
     * @return bool
     */
    public function fileExists(string $locale = null, $size = null): bool
    {
        $fileName = null === $locale ? $this->value : $this->translateOrNew($locale)->value;

        if ($fileName === null) {
            return false;
        }

        $storage = $this->getStorage();

        $settings = $this->image_settings;

        if (($sizes = array_get($settings, 'sizes')) !== null) {
            $size = $size !== null ? $size : array_get($settings, 'default');

            return $storage->exists(implode(
                '/',
                array_filter([Setting::STORAGE_DIR, array_get($settings, 'directory'), $size, $fileName])
            ));
        }

        return $storage->exists(Setting::STORAGE_DIR . '/' . $fileName);
    }

    /**
     * @param  string|null  $locale
     * @return string|null
     */
    public function getFileUrl(string $locale = null): ?string
    {
        $fileName = null === $locale ? $this->value : $this->translateOrNew($locale)->value;

        if ($fileName === null) {
            return false;
        }

        $storage = $this->fileStorage();

        $path = implode('/', [static::STORAGE_DIR, $fileName]);

        return $storage->exists($path) ? $storage->url($path) : null;
    }

    /**
     * @param  bool  $formatted
     * @param  string|null  $locale
     * @param  null  $size
     * @return mixed
     */
    public function getFileSize(bool $formatted = true, string $locale = null, $size = null)
    {
        if (!$this->fileExists($locale)) {
            return null;
        }

        $fileName = null === $locale ? $this->value : $this->translateOrNew($locale)->value;

        $settings = $this->image_settings;
        if (($sizes = array_get($settings, 'sizes')) !== null) {
            $size = $size !== null ? $size : array_get($settings, 'default');

            $path = implode(
                '/',
                array_filter([Setting::STORAGE_DIR, array_get($settings, 'directory'), $size, $fileName])
            );
        } else {
            $path = implode('/', [static::STORAGE_DIR, $fileName]);
        }

        $fileSize = $this->getStorage()->size($path);

        if (!$fileSize) {
            return null;
        }

        return $formatted ? Helpers::bytesToHuman($fileSize) : $fileSize;
    }

    /**
     * @param  string|null  $locale
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
     * @param  string|null  $locale
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
     * @param  \Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|array|null  $file
     * @param  AbstractField|\WezomCms\Core\Settings\Fields\Image  $field
     * @return string
     */
    private function saveImage($file, AbstractField $field)
    {
        $setting = $field->extractSettings();

        $clientOriginalExtension = $file->getClientOriginalExtension();

        $fileName = Str::random(40) . '.' . $clientOriginalExtension;

        $path = implode('/', array_filter([self::STORAGE_DIR, array_get($setting, 'directory')]));

        $storage = $this->imageStorage($setting);

        $dontModify = in_array($clientOriginalExtension, ['svg', 'gif']);
        if ($sizes = array_get($setting, 'sizes', [])) {
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
        } else {
            $storage->makeDirectory($path);
            $storage->putFileAs($path, $file, $fileName);
        }

        return $fileName;
    }

    /**
     * @param  string|null  $size
     * @param  string|null  $field
     * @param  string|null  $locale
     * @return string|null
     */
    public function getImageUrl(string $size = null, string $field = null, string $locale = null)
    {
        $setting = $this->image_settings;

        $size = null !== $size ? $size : array_get($setting, 'default'); // Select default image size.


        if (!$setting) {
            $size = null;
        }

        $storage = $this->imageStorage();

        if ($this->imageExists($size, $field, $locale)) {
            $pathParts = [
                static::STORAGE_DIR,
                array_get($setting, 'directory'),
                $size,
                $this->getStoredFileName($setting, $locale),
            ];

            $path = implode('/', array_filter($pathParts));
        } else {
            $sizeSetting = array_get($setting, 'sizes.' . $size, []);

            // Make placeholder
            $path = ImageHandler::noImage(
                array_get($sizeSetting, 'width'),
                array_get($sizeSetting, 'height'),
                array_get($setting, 'placeholder'),
                $storage
            );
        }

        if (ImageService::webPSupport() && $storage->exists($path . '.webp')) {
            return $storage->url($path . '.webp');
        }

        return $storage->url($path);
    }

    /**
     * @return string
     */
    public function getImageExtension(): string
    {
        return pathinfo($this->value, PATHINFO_EXTENSION);
    }

    /**
     * @return bool
     */
    public function isSvg(): bool
    {
        return mb_strtolower($this->getImageExtension()) === 'svg';
    }

    /**
     * @param  string|null  $size
     * @param  string|null  $field
     * @return null|string
     */
    public function getSvgContent(string $size = null, string $field = null)
    {
        $fileName = $this->value;
        if (!$fileName) {
            return null;
        }

        $setting = $this->image_settings;

        $size = null !== $size ? $size : array_get($setting, 'default'); // Select default image size.

        if (!$setting) {
            $size = null;
        }

        $path = implode('/', array_filter([static::STORAGE_DIR, array_get($setting, 'directory'), $size, $fileName]));

        try {
            return $this->imageStorage()->get($path);
        } catch (FileNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param  string|null  $size
     * @param  string|null  $field
     * @param  string|null  $locale
     * @return mixed
     */
    public function getExistImageUrl(string $size = null, string $field = null, string $locale = null)
    {
        return $this->imageExists($size, $field, $locale) ? $this->getImageUrl($size, $field, $locale) : null;
    }

    /**
     * @param  string|null  $size
     * @param  string|null  $field
     * @param  string|null  $locale
     * @return bool
     */
    public function imageExists(string $size = null, string $field = null, string $locale = null)
    {
        $setting = $this->image_settings;

        $storedFileName = $this->getStoredFileName($setting, $locale);
        if (!$storedFileName) {
            return false;
        }

        $size = null !== $size ? $size : array_get($setting, 'default'); // Select default image size.

        if (!$setting) {
            $size = null;
        }

        $path = implode('/', array_filter([
            static::STORAGE_DIR,
            array_get($setting, 'directory'),
            $size,
            $storedFileName
        ]));

        return $this->imageStorage()->exists($path);
    }

    /**
     * @param  string  $field
     * @return array|null
     */
    public function getRecommendUploadImageSize(string $field = 'image'): ?array
    {
        $setting = $this->image_settings;

        if (!$setting) {
            return null;
        }

        $sizes = array_get($setting, 'sizes', []);

        if (!$sizes) {
            return null;
        }

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
     * @param  array  $setting
     * @param  string|null  $locale
     * @return mixed
     */
    private function getStoredFileName(array $setting, string $locale = null)
    {
        return array_get($setting, 'multilingual', false)
            ? $this->translateOrNew($locale)->getAttribute('value')
            : $this->getAttribute('value');
    }

    /**
     * @param  string|null  $type
     * @return \Illuminate\Contracts\Filesystem\Filesystem|null
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
     * @param  array|null  $setting
     * @return Filesystem
     */
    protected function imageStorage(?array $setting = null): Filesystem
    {
        if ($setting === null) {
            $setting = $this->image_settings;
        }

        return Storage::disk(array_get($setting, 'storage', config('cms.core.image.storage')));
    }

    /**
     * @param  array|null  $setting
     * @return Filesystem
     */
    protected function fileStorage(?array $setting = null): Filesystem
    {
        if ($setting === null) {
            $setting = $this->image_settings;
        }

        return Storage::disk(array_get($setting, 'storage', config('cms.core.files.storage')));
    }
}
