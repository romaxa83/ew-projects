<?php

namespace App\Models\History;

use App\ModelFilters\History\HistoryFilter;
use App\Models\Users\User;
use Database\Factories\History\HistoryFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int|null user_id
 * @property int|null type
 * @property int|null model_id
 * @property array|null meta
 * @property array|null histories
 * @property int|null dispatcher_id
 * @property string|null message
 * @property Carbon|null performed_at
 * @property-read  User|null user
 * @method static Builder|static whereType($value)
 *
 * @mixin Eloquent
 *
 * @method static HistoryFactory factory(...$parameters)
 *
 */
class History extends Model
{
    use Filterable;
    use HasFactory;

    public const TABLE_NAME = 'histories';
    public const TYPE_ACTIVITY = 0;
    public const TYPE_CHANGES = 1;
    public $timestamps = false;
    protected $table = self::TABLE_NAME;
    protected $dates = ['performed_at'];
    protected $casts = [
        'meta' => 'array',
        'histories' => 'array'
    ];
    protected $guarded = [];
    protected $hidden = [];

    /**
     * Get the user who performed this record
     */
    public function user()
    {
        /** @var BelongsTo|User $belongsTo */
        $belongsTo = $this->belongsTo(User::class);

        return $belongsTo->withTrashed();
    }

    public function hasUser(): bool
    {
        return !empty($this->user_role) && !empty($this->user_id);
    }

    /**
     * Get the model of this record
     */
    public function model()
    {
        return $this->morphTo()->first();
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(HistoryFilter::class);
    }
}
