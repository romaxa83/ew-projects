<?php

namespace App\Models\Companies;

use App\Filters\Companies\CorporationFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Companies\CorporationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string guid
 * @property string name
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see Corporation::companies()
 * @property-read Company|Collection companies
 *
 * @method static CorporationFactory factory(...$options)
 */
class Corporation extends BaseModel
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'corporations';
    protected $table = self::TABLE;

    protected $fillable = [
        'guid'
    ];

    public function modelFilter(): string
    {
        return CorporationFilter::class;
    }

    public function companies(): HasMany
    {
        return $this->HasMany(Company::class);
    }
}
