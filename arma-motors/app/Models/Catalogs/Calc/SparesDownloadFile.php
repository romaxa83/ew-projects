<?php

namespace App\Models\Catalogs\Calc;

use App\Models\BaseModel;
use App\Models\Media\File;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property string $type
 * @property string $status
 * @property string $file_name
 * @property string $error_message
 *
 */
class SparesDownloadFile extends BaseModel
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PROCESS = 'process';
    const STATUS_ERROR = 'error';
    const STATUS_DONE = 'done';

    public const TABLE = 'spares_download_files';
    protected $table = self::TABLE;

    public static function createRecord(
        $type,
        $file_name
    )
    {
        Spares::assetType($type);

        $model = new self();
        $model->type = $type;
        $model->status = self::STATUS_DRAFT;
        $model->file_name = $file_name;
        $model->save();

        return $model;
    }

    public function toggleStatusProcess(): self
    {
        $this->status = self::STATUS_PROCESS;
        $this->save();

        return $this;
    }

    public function toggleStatusDone(): self
    {
        $this->status = self::STATUS_DONE;
        $this->save();

        return $this;
    }

    public function toggleStatusError($error = null): self
    {
        $this->status = self::STATUS_ERROR;
        $this->error_message = $error;
        $this->save();

        return $this;
    }

    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'entity');
    }
}
