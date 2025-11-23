<?php

namespace App\Models\Zadarma;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Zadarma\CallsRecords
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CallsRecords newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CallsRecords newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CallsRecords query()
 * @property int $id
 * @property string|null $event
 * @property string|null $pbx_id
 * @property string|null $pbx_call_id
 * @property string|null $call_id_with_rec
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CallsRecords whereCallIdWithRec($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallsRecords whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallsRecords whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallsRecords whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallsRecords wherePbxCallId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallsRecords wherePbxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallsRecords whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CallsRecords extends Model
{
    use HasFactory;

    protected $table = 'zadarma_calls_records';
    protected $fillable = [
        'event', 'pbx_id', 'pbx_call_id', 'call_id_with_rec'
    ];

}
