<?php

namespace App\Models\Reports;

use App\Filters\Reports\PauseItemFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Database\Factories\Reports\PauseItemFactory;

/**
 * @property int id
 * @property int report_id
 * @property Carbon|null pause_at
 * @property Carbon unpause_at
 * @property array data
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @method static PauseItemFactory factory(int $number = null)
 */
class PauseItem extends BaseModel
{
    use HasFactory;
    use Filterable;

    protected $table = self::TABLE;
    public const TABLE = 'report_pause_items';

    protected $fillable = [];

    protected $dates = [
        'pause_at',
        'unpause_at'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function modelFilter(): string
    {
        return PauseItemFilter::class;
    }

    public function getDiffAtBySec(): int
    {
        return $this->pause_at->diffInSeconds($this->unpause_at);
    }
}
