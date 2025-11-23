<?php

namespace App\Models\Order;

use App\Models\Division;
use Database\Factories\Orders\SourceFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, SoftDeletes, Model};
use OwenIt\Auditing\Auditable as AuditableTrait;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\Source
 *
 * @property int $id
 * @property string $title
 * @property string|null $color
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Source newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Source newQuery()
 * @method static \Illuminate\Database\Query\Builder|Source onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Source query()
 * @method static \Illuminate\Database\Eloquent\Builder|Source whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Source whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Source whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Source whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|Source withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Source withoutTrashed()
 * @property string|null $division_ids
 * @method static \Illuminate\Database\Eloquent\Builder|Source whereDivisionIds($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 *
 * @method static SourceFactory factory(...$parameters)
 */
class Source extends Model implements Auditable
{
    use AuditableTrait;
    use HasJsonRelationships;
    use SoftDeletes;
    use HasFactory;

    public const GOOGLE_GUARANTEE_NAME= 'Google Guarantee';

    public const TABLE = 'orders_sources';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'division_ids' => 'json',
    ];

    protected $fillable = [
        'title',
        'color',
        'division_ids'
    ];

    protected static function newFactory(): SourceFactory
    {
        return SourceFactory::new();
    }

    public function divisions()
    {
        return $this->belongsToJson(Division::class, 'division_ids');
    }
}
