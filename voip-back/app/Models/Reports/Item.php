<?php

namespace App\Models\Reports;

use App\Enums\Reports\ReportStatus;
use App\Filters\Reports\ItemFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Database\Factories\Reports\ItemFactory;

/**
 * @property int id
 * @property int report_id
 * @property string|null callid
 * @property ReportStatus status
 * @property string num
 * @property string|null name
 * @property int wait
 * @property int total_time
 * @property Carbon|null call_at
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @method static ItemFactory factory(int $number = null)
 */
class Item extends BaseModel
{
    use HasFactory;
    use Filterable;

    protected $table = self::TABLE;
    public const TABLE = 'report_items';

    public const UNKNOWN = 'unknown';

    protected $fillable = [];

    protected $dates = [
        'call_at'
    ];

    protected $casts = [
        'status' => ReportStatus::class,
    ];

    public function modelFilter(): string
    {
        return ItemFilter::class;
    }

    public function getName(): ?string
    {
        if($this->name === self::UNKNOWN){
            return null;
        }
        return $this->name;
    }

    public function getNum(): ?string
    {
        if($this->num === self::UNKNOWN){
            return null;
        }
        return $this->num;
    }
}
