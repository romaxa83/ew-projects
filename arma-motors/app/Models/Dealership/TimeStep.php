<?php

namespace App\Models\Dealership;

use App\Models\BaseModel;
use App\Models\Catalogs\Service\Service;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $step       // временной шаг, для заявок, (миллисекунды)
 * @property int $dealership_id
 * @property int $service_id
 */
class TimeStep extends BaseModel
{
    public $timestamps = false;

    public const DEFAULT = 3600000; // 1h

    public const TABLE = 'dealership_time_steps';

    protected $table = self::TABLE;

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
