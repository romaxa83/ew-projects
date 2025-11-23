<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CommunicationsIgnoreList
 *
 * @property int $id
 * @property string $type
 * @property string|null $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationsIgnoreList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationsIgnoreList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationsIgnoreList query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationsIgnoreList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationsIgnoreList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationsIgnoreList whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationsIgnoreList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationsIgnoreList whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationsIgnoreList email() Scope to filter only email type records
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationsIgnoreList phone() Scope to filter only phone type records
 * @mixin \Eloquent
 *
 * Available Scopes:
 * - email(): Filters records to only include those with type 'emails'
 * - phone(): Filters records to only include those with type 'phones'
 */
class CommunicationsIgnoreList extends Model
{
    public const TYPE_PHONE = 'phones';
    public const TYPE_EMAIL = 'emails';

    public const TABLE = 'clients_communications_ignore';
    protected $table = self::TABLE;

    protected $fillable = [
        'type',
        'value'
    ];

    /**
     * Scope a query to only include email type records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEmail($query)
    {
        return $query->where('type', self::TYPE_EMAIL);
    }

    /**
     * Scope a query to only include phone type records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePhone($query)
    {
        return $query->where('type', self::TYPE_PHONE);
    }
}
