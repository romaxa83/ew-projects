<?php

namespace App\Models\AA;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * существуящая заявка в системе АА, может быть как создана через
 * МП, так и в самих дилерских центрах, содержат в себе информацию
 * о времени, на которое эта заявка назначена , что бы в дальнейшем высчитать
 * свободное время, приходит в систему когда верифецируется системе АА (пришедшая из МП)
 * или когда создается на цех в дц
 *
 * @property int $id
 * @property string|null $order_uuid            // в данных это [id]
 * @property string|null $user_uuid             // в данных это [client]
 * @property string|null $car_uuid              // в данных это [auto]
 * @property string|null $service_alias         // в данных это [type]
 * @property string|null $sub_service_alias     // в данных это [subType]
 * @property string|null $dealership_alias      // в данных это [base]
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property string $post_uuid                  // в данных это [workshop]
 * @property string|null $comment
 * @property bool $is_sys                       // даная заявка создана через данную систему, или создана только в системе АА
 *
 */
class AAOrder extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'aa_orders';
    protected $table = self::TABLE;

    protected $casts = [
        'is_sys' => 'boolean',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(AAPost::class, "post_uuid","uuid");
    }

    public function planning(): HasMany
    {
        return $this->hasMany(AAOrderPlanning::class, "aa_order_id", "id");
    }
}
