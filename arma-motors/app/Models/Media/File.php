<?php

namespace App\Models\Media;

use App\Models\BaseModel;
use Carbon\Carbon;

/**
 * App\Models\Media\File
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
class File extends BaseModel
{
    public const MODEL_CAR = 'car';
    public const MODEL_ORDER = 'order';
    public const MODEL_PAGE = 'page';
    public const MODEL_SPARES = 'spares';

    public const TABLE_NAME = 'files';

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
        return \Asset($this->getPath());
    }

    public function getPathAttribute(): string
    {
        return storage_path() . "/app/public/files/{$this->model}/{$this->entity_id}/{$this->hash}";
    }

    public function pathToFileStorage(): string
    {
        return \Storage::getDriver()->getAdapter()->getPathPrefix() . $this->getPath( 'public');
    }

    public function pathToFolderStorage(): string
    {
        return \Storage::getDriver()->getAdapter()->getPathPrefix() . "public/files/{$this->model}/{$this->entity_id}/";
    }

    private function getPath(string $storageFolder = 'storage')
    {
        return "{$storageFolder}/files/{$this->model}/{$this->entity_id}/{$this->hash}";
    }
}
