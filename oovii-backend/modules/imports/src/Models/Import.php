<?php

namespace WezomCms\Imports\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use WezomCms\Core\Contracts\BelongsToAdminInterface;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Scopes\BelongsToAdminScope;
use WezomCms\Core\Traits\Model\FileAttachable;

/**
 * @property int $id
 * @property int|null $administrator_id
 * @property string $type
 * @property string $status
 * @property string|null $message
 * @property string|null $error_data
 * @property string|null $file
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @see Import::administrator()
 * @property-read Administrator $administrator
 */
class Import extends EloquentModel implements BelongsToAdminInterface
{
    use FileAttachable;

    public const STATUS_NEW        = 'new';
    public const STATUS_IN_PROCESS = 'in_process';
    public const STATUS_FAILED     = 'failed';
    public const STATUS_DONE       = 'done';

    public const TYPE_PRODUCT = 'product';

    public const TABLE = 'imports';
    protected $table = self::TABLE;

    protected $fillable = [
        'type',
        'status',
        'message',
        'error_data',
        'file',
    ];

    public $casts = [
        'error_data' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BelongsToAdminScope());
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_NEW => __('cms-imports::admin.status.new'),
            self::STATUS_DONE => __('cms-imports::admin.status.done'),
            self::STATUS_FAILED => __('cms-imports::admin.status.failed'),
            self::STATUS_IN_PROCESS => __('cms-imports::admin.status.in_process'),
        ];
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isInProcess(): bool
    {
        return $this->status === self::STATUS_IN_PROCESS;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function render(): string
    {
        if($this->isNew()){
            return '<span class="badge badge-info">'. self::statusList()[self::STATUS_NEW].'</span>';
        }
        if($this->isInProcess()){
            return '<span class="badge badge-warning">'. self::statusList()[self::STATUS_IN_PROCESS].'</span>';
        }
        if($this->isFailed()){
            return '<span class="badge badge-danger">'. self::statusList()[self::STATUS_FAILED].'</span>';
        }
        return '<span class="badge badge-success">'. self::statusList()[self::STATUS_DONE].'</span>';
    }

    public function fileSettings(): array
    {
        return ['file' => ['directory' => 'imports']];
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }
}
