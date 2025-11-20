<?php

namespace WezomCms\Core\Traits\Model;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use WezomCms\Core\Files\FileService;

/**
 * Trait FileAttachable
 * @package WezomCms\Core\Traits\Model
 */
trait FileAttachable
{
    /**
     * Files configuration.
     * Example:
     * return [
     *     'file' => 'cms.core.administrator.file', // Set path to configuration array
     *     // or
     *     'file' => [ // array key - field name in DB.
     *         'directory' => 'administrator_files', // The name of the directory to store downloadable file.
     *         'storage' => 'public', // Storage driver name. Not required.
     *         'original_name' => 'file_original_name' || true || false, // If "true" - app generate DB field like
     *                                                                      "{array key}_original_name".
     *                                                                      Default value is "false".
     *         'multilingual' => true || false, // File is multilingual.
     *     ],
     *     // Another fields
     * ];
     * @return array
     */
    abstract public function fileSettings(): array;

    /**
     * @param  SymfonyUploadedFile|SymfonyUploadedFile[]|array|mixed $source
     * @param  string  $field
     * @return bool
     */
    public function uploadFile($source, string $field = FileService::FILE): bool
    {
        return app(FileService::class)->uploadFile($this, $source, $field);
    }

    /**
     * @param  string  $field
     * @return bool
     */
    public function isFileMultilingual(string $field = FileService::FILE): bool
    {
        $fileService = app(FileService::class);

        return $fileService->isMultilingual($fileService->extractSetting($this, $field));
    }

    /**
     * Getting the URL of the downloaded file.
     *
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     */
    public function getFileUrl(string $field = FileService::FILE, string $locale = null): ?string
    {
        return app(FileService::class)->getFileUrl($this, $field, $locale);
    }

    /**
     * Check for file existence.
     *
     * @param  string  $field
     * @param  string|null  $locale
     * @return bool
     */
    public function fileExists(string $field = FileService::FILE, string $locale = null): bool
    {
        return app(FileService::class)->fileExists($this, $field, $locale);
    }

    /**
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     */
    public function getFileName(string $field = FileService::FILE, string $locale = null): ?string
    {
        return app(FileService::class)->getFileName($this, $field, $locale);
    }

    /**
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     */
    public function getOriginalFileName(string $field = FileService::FILE, string $locale = null): ?string
    {
        return app(FileService::class)->getOriginalFileName($this, $field, $locale);
    }

    /**
     * @param  bool  $formatted
     * @param  string  $field
     * @param  string|null  $locale
     * @return mixed
     */
    public function getFileSize(bool $formatted = true, string $field = FileService::FILE, string $locale = null)
    {
        return app(FileService::class)->getFileSize($this, $formatted, $field, $locale);
    }

    /**
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     */
    public function getFileExtension(string $field = FileService::FILE, string $locale = null): ?string
    {
        return app(FileService::class)->getFileExtension($this, $field, $locale);
    }

    /**
     * Delete file if exists.
     *
     * @param  string  $field
     * @param  string|null  $locale
     * @return bool
     */
    public function deleteFile(string $field = FileService::FILE, string $locale = null): bool
    {
        return app(FileService::class)->deleteFile($this, $field, null, $locale);
    }

    /**
     * @param  bool  $save
     * @return bool
     */
    public function deleteAllFiles(bool $save = false): bool
    {
        return app(FileService::class)->deleteAllFiles($this, $save);
    }

    /**
     * Hook into the Eloquent model events.
     */
    public static function bootFileAttachable()
    {
        static::deleting(function (Model $model) {
            /** @var $model FileAttachable */
            $model->deleteAllFiles();
        });
    }
}
