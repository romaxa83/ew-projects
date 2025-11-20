<?php

namespace App\Traits;

use Exception;
use Storage;

/**
 * Trait Uploads
 * @package App\Traits
 *
 * Works with images
 *
 * @property-read boolean $is_file_exists
 * @property-read string $file_link
 */
trait Files
{
    /**
     * Name of the column in database
     *
     * @return string
     */
    public function fileColumnName()
    {
        return 'file';
    }

    /**
     * Upload file to storage & link it with current model
     *
     * @param string $formFieldName
     * @param string $folderName
     * @throws Exception
     */
    public function upload($formFieldName, $folderName)
    {
        $column = $this->fileColumnName();

        $file = request()->file($formFieldName);
        $fileName = "{$folderName}/" . time() . '.' . $file->getClientOriginalExtension();

        if (Storage::disk(config('filesystems.default'))->put('public/'. $fileName, file_get_contents($file))) {
            $this->{$column} = $fileName;
        } else {
            throw new Exception('Failed to upload file', 500);
        }
    }

    /**
     * Generate link to thumb. Image will be cropped and cached
     */
    public function getFileLinkAttribute(): ?string
    {
        if ($this->is_file_exists) {
            return url("storage/{$this->{$this->fileColumnName()}}");
        }

        return null;
    }

    /**
     * Check if image exists
     *
     * @return bool
     */
    public function getIsFileExistsAttribute(): bool
    {
        return Storage::exists('public/' . $this->{$this->fileColumnName()});
    }

}
