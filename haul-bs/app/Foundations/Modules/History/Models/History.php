<?php

namespace App\Foundations\Modules\History\Models;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Factories\HistoryFactory;
use App\Foundations\Modules\History\Filters\HistoryFilter;
use App\Foundations\Traits\Filters\Filterable;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int id
 * @property HistoryType type
 * @property string model_type
 * @property int model_id
 * @property int|null user_id
 * @property string|null user_role
 * @property string msg
 * @property string msg_attr
 * @property Carbon performed_at
 * @property string|null performed_timezone
 * @property string|null details
 *
 * @see self::initiator()
 * @property-read User|BelongsTo initiator
 *
 * @method static HistoryFactory factory(...$parameters)
 */
class History extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'histories';
    protected $table = self::TABLE;

    public $timestamps = false;

    /** @var array<int, string> */
    protected $dates = [
        'performed_at'
    ];

    /** @var array<string, string> */
    protected $casts = [
        'type' => HistoryType::class,
        'msg_attr' => 'array',
        'details' => 'array',
    ];

    public function modelFilter(): string
    {
        return HistoryFilter::class;
    }

    public function initiator(): BelongsTo
    {
        /** @var BelongsTo|User $belongsTo */
        $belongsTo = $this->belongsTo(User::class, 'user_id', 'id');

        return $belongsTo->withTrashed();
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): HistoryFactory
    {
        return HistoryFactory::new();
    }
}
