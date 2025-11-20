<?php

namespace App\Models\Import;

use App\Models\BaseModel;
use App\Models\User\User;
use App\Traits\Files;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IosLinkImport extends BaseModel
{
    const STATUS_NEW = 'new';
    const STATUS_IN_PROCESS = 'in_process';
    const STATUS_FAILED = 'failed';
    const STATUS_DONE = 'done';

    use Files;
    use HasFactory;


    const TABLE = 'ios_link_imports';
    public $table = self::TABLE;

    public $fillable = [
        'user_id',
        'file',
        'message',
        'status',
        'error_data'
    ];

    public $casts = [
        'error_data' => 'array',
    ];

    public function isNew(): bool
    {
        return $this->status == self::STATUS_NEW;
    }

    public function isInProcess(): bool
    {
        return $this->status == self::STATUS_IN_PROCESS;
    }

    public function isFailed(): bool
    {
        return $this->status == self::STATUS_FAILED;
    }

    public function isDone(): bool
    {
        return $this->status == self::STATUS_DONE;
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
