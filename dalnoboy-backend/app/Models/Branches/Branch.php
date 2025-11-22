<?php

namespace App\Models\Branches;

use App\Filters\Branches\BranchFilter;
use App\Models\BaseModel;
use App\Models\Inspections\Inspection;
use App\Models\Locations\Region;
use App\Models\Users\User;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasPhones;
use Database\Factories\Branches\BranchFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static BranchFactory factory()
 */
class Branch extends BaseModel
{
    use HasFactory;
    use HasPhones;
    use Filterable;
    use ActiveScopeTrait;

    public const TABLE = 'branches';

    public const ALLOWED_ORDERED_FIELDS = [
        'name',
        'city',
        'region',
        'total_employees'
    ];

    protected $fillable = [
        'name',
        'city',
        'region_id',
        'address',
        'active',
        'created_at',
        'updated_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_branch',
            'branch_id',
            'user_id',
            'id',
            'id'
        );
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'branch_id', 'id')
            ->orderByDesc('id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(BranchFilter::class);
    }
}
