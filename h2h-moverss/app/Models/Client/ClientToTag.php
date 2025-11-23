<?php

namespace App\Models\Client;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $client_id
 * @property int $tag_id
 * @property int|null $employee_id
 * @property string|null $employee_name
 * @property Carbon|null $attached_at
 *
 * @property-read  Employee|null $employee

 */
class ClientToTag extends Pivot
{
    public const TABLE = 'clients_2_tags';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'tag_id',
        'employee_id',
        'employee_name',
        'attached_at',
    ];

    protected $dates = [
        'attached_at',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}