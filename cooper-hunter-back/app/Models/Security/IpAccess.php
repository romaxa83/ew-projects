<?php

namespace App\Models\Security;

use App\Filters\Security\IpAccessFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use Database\Factories\Security\IpAccessFactory;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string address
 * @property null|string description
 * @property bool active
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @method static IpAccessFactory factory(...$options)
 */
class IpAccess extends BaseModel
{
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;

    public const ACTIVE = true;

    public const TABLE = 'ip_access_list';

    public const ALLOWED_SORTING_FIELDS = [
        'address',
        'description',
        'active'
    ];

    protected $table = self::TABLE;

    protected $fillable = [
        'address',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $attributes = [
        'active' => true,
    ];

    public function modelFilter(): string
    {
        return IpAccessFilter::class;
    }
}
