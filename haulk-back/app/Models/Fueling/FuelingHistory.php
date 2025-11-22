<?php

namespace App\Models\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelingHistoryStatusEnum;
use App\ModelFilters\Fueling\FuelingHistoryFilter;
use App\Models\Users\User;
use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use Database\Factories\Fueling\FuelingHistoryFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Tags\Tag
 *
 * @property int $id
 * @property integer $total
 * @property integer $count_errors
 * @property integer $counts_success
 * @property integer $progress
 * @property string $path_file
 * @property string $original_name
 * @property FuelingHistoryStatusEnum|string|null $status
 * @property FuelCardProviderEnum|string $provider
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 * @property int|null $user_id
 *
 * @property int|null broker_id
 * @property int|null carrier_id
 *
 * @mixin Eloquent
 *
 * @see self::user()
 * @property User|HasOne|null user
 *
 * @method static FuelingHistoryFactory factory(...$parameters)
 */
class FuelingHistory extends Model
{
    use Filterable;
    use SetCompanyId;
    use HasFactory;

    public const TABLE_NAME = 'fueling_history';

    protected $table = self::TABLE_NAME;

    /**
     * @var array
     */
    protected $fillable = [
        'started_at',
        'ended_at',
        'total',
        'count_errors',
        'counts_success',
        'progress',
        'path_file',
        'original_name',
        'status',
        'provider',
        'user_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

    public function modelFilter(): string
    {
        return FuelingHistoryFilter::class;
    }

    public function inProgress(): void
    {
        $this->started_at = now();
        $this->status = FuelingHistoryStatusEnum::IN_PROGRESS;
        $this->save();
    }

    public function getProgress(): float
    {
        return $this->total ? ($this->progress * 100 / $this->total) : 0;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id',
        );
    }

    public function ended(): void
    {
        $this->ended_at = now();
        $this->status = FuelingHistoryStatusEnum::SUCCESS;
        $this->save();
    }

    public function failed(): void
    {
        $this->ended_at = now();
        $this->status = FuelingHistoryStatusEnum::FAILED;
        $this->save();
    }
}
