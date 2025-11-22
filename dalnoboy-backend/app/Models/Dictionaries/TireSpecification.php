<?php

namespace App\Models\Dictionaries;

use App\Contracts\Models\HasModeration;
use App\Filters\Dictionaries\TireSpecificationFilter;
use App\Models\BaseModel;
use App\Models\Tires\Tire;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\ModeratedScopeTrait;
use Database\Factories\Dictionaries\TireSpecificationFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static TireSpecificationFactory factory()
 */
class TireSpecification extends BaseModel implements Sortable, HasModeration
{
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use ModeratedScopeTrait;

    public const TABLE = 'tire_specifications';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public $fillable = [
        'make_id',
        'model_id',
        'type_id',
        'size_id',
        'ngp',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TireSpecificationFilter::class);
    }

    public function tireMake(): BelongsTo|TireMake
    {
        return $this->belongsTo(TireMake::class, 'make_id');
    }

    public function tireModel(): BelongsTo|TireModel
    {
        return $this->belongsTo(TireModel::class, 'model_id');
    }

    public function tireType(): BelongsTo|TireType
    {
        return $this->belongsTo(TireType::class, 'type_id');
    }

    public function tireSize(): BelongsTo|TireSize
    {
        return $this->belongsTo(TireSize::class, 'size_id');
    }

    public function tires(): HasMany|Tire
    {
        return $this->hasMany(Tire::class, 'specification_id');
    }

    public function shouldModerated(): bool
    {
        if (!$this->isModerated()) {
            return true;
        }

        if ($this->tireMake->shouldModerated()) {
            return true;
        }

        if ($this->tireModel->shouldModerated()) {
            return true;
        }

        if ($this->tireSize->shouldModerated()) {
            return true;
        }

        return false;
    }
}
