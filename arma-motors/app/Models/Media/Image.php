<?php

namespace App\Models\Media;

use App\Models\Admin\Admin;
use App\Models\BaseModel;
use App\Services\Media\Image\ImageService;
use Carbon\Carbon;

/**
 * App\Models\Media\Image
 *
 * @property int $id
 * @property string $entity_type
 * @property int $entity_id
 * @property string $model
 * @property string $type
 * @property string $basename
 * @property string $hash
 * @property boolean $active
 * @property int $position
 * @property string $mime
 * @property string $ext
 * @property string $size
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 */
class Image extends BaseModel
{
    public const MODEL_USER = 'user';
    public const MODEL_ADMIN = 'admin';
    public const MODEL_DEALERSHIP = 'dealership';
    public const MODEL_MODEL = 'model';
    public const MODEL_BRAND = 'brand';
    public const MODEL_CAR = 'car';
    public const MODEL_PROMOTION = 'promotion';

    public const TABLE_NAME = 'images';

    protected $table = self::TABLE_NAME;

    protected $casts = [
        'active' => 'boolean',
    ];

    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * @example http://192.168.177.1/storage/user/4/original/Qel30O7BV4jbdj1wdA6qCYGJOSh7yum9pwi79fDy.png
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return \Asset($this->getPath(ImageService::ORIGINAL));
    }

    public function getSizesAttribute()
    {
        $config = config('image.models');
        $sizes = $config[$this->model]['sizes'] ?? [];

        $data[0]['size'] = ImageService::ORIGINAL;
        $data[0]['url'] = \Asset($this->getPath(ImageService::ORIGINAL));
        $count = 1;
        foreach ($sizes as $nameSize => $size){
            $data[$count]['size'] = $nameSize;
            $data[$count]['url'] = \Asset($this->getPath($nameSize));
            $count++;
        }

        return $data;
    }

    public function pathToFileStorage($size = ImageService::ORIGINAL): string
    {
        return \Storage::getDriver()->getAdapter()->getPathPrefix() . $this->getPath($size, 'public');
    }

    public function pathToFolderStorageSize($size = ImageService::ORIGINAL): string
    {
        return \Storage::getDriver()->getAdapter()->getPathPrefix() . self::getPathFolder($size, 'public');
    }

    public function pathToFolderStorage($size = ImageService::ORIGINAL): string
    {
        return \Storage::getDriver()->getAdapter()->getPathPrefix() . "public/{$this->model}/{$this->entity_id}/";
    }

    private function getPath(string $size, string $storageFolder = 'storage')
    {
        return "{$storageFolder}/{$this->model}/{$this->entity_id}/{$size}/{$this->hash}";
    }

    private function getPathFolder(string $size, string $storageFolder = 'storage')
    {
        return "{$storageFolder}/{$this->model}/{$this->entity_id}/{$size}/";
    }
}
