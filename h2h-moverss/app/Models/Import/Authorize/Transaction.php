<?php

namespace App\Models\Import\Authorize;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Import\Authorize\Transaction
 *
 * @property int $id
 * @property int $account_id
 * @property string $status
 * @property string $amount
 * @property mixed|null $miscs JSON
 * @property string $submitTimeUTC Дата транзакции
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Import\Authorize\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereSubmitTimeUTC($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    protected $table = 'authorize_transactions';
    protected $casts = [
        'miscs' => 'json',
    ];
    protected $fillable = ['id', 'account_id', 'status', 'amount', 'submitTimeUTC', 'miscs'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

}
