<?php

namespace App\Models;

use Database\Factories\Orders\PaymentAccountFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, SoftDeletes};

/**
 * App\Models\PaymentAccount
 *
 * @property int $id
 * @property int $is_active
 * @property int|null $division_id
 * @property string $title
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount newQuery()
 * @method static \Illuminate\Database\Query\Builder|PaymentAccount onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount records()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PaymentAccount withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PaymentAccount withoutTrashed()
 * @property int|null $sort
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccount whereSort($value)
 * @mixin \Eloquent
 * @method static PaymentAccountFactory factory(...$parameters)
 */
class PaymentAccount extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const TABLE = 'payments_accounts';
    protected $table = self::TABLE;

    protected $dates = [
        'deleted_at'
    ];

    protected $fillable = [
        'title',
        'is_active',
        'division_id',
        'sort'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): PaymentAccountFactory
    {
        return PaymentAccountFactory::new();
    }

    public function scopeRecords($query)
    {
        return $query
            ->where('is_active', 1)
            ->orderBy('sort')
            ->select(['id', 'title']);
    }
}
