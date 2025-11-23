<?php

namespace App\Models\Import\Authorize;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Import\Authorize\Account
 *
 * @property int $id
 * @property int $division_id
 * @property int|null $payment_account_id Add transaction to payment account
 * @property string $title
 * @property string $login
 * @property string $transactionKey
 * @property int $active
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Import\Authorize\Transaction[] $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereTransactionKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account wherePaymentAccountId($value)
 * @mixin \Eloquent
 */
class Account extends Model
{
    protected $table = 'authorize_accounts';
    protected $fillable = ['title', 'active', 'payment_account_id', 'login', 'transactionKey'];
    public $timestamps = false;

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_id', 'id');
    }
}
