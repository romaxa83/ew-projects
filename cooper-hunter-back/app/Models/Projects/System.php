<?php

namespace App\Models\Projects;

use App\Casts\Warranty\WarrantyStatusCast;
use App\Contracts\Alerts\AlertModel;
use App\Enums\Projects\Systems\WarrantyStatus;
use App\Events\Systems\SystemUpdatedEvent;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Models\Warranty\WarrantyRegistration;
use App\Traits\HasFactory;
use Database\Factories\Projects\SystemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int project_id
 * @property WarrantyStatus warranty_status
 * @property string name
 * @property string|null description
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see System::project()
 * @property-read Project project
 *
 * @see System::units()
 * @property-read Collection|Product[] units
 *
 * @method static SystemFactory factory(...$parameters)
 */
class System extends BaseModel implements AlertModel
{
    use HasFactory;

    public const TABLE = 'systems';

    public const MORPH_NAME = 'system';

    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'description',
        'project_id',
    ];

    protected $casts = [
        'warranty_status' => WarrantyStatusCast::class,
    ];

    protected $dispatchesEvents = [
        'updated' => SystemUpdatedEvent::class,
    ];

    public function project(): BelongsTo|Project
    {
        return $this->belongsTo(Project::class);
    }

    public function units(): BelongsToMany|Product
    {
        return $this->belongsToMany(Product::class, SystemUnitPivot::TABLE)
            ->using(SystemUnitPivot::class)
            ->as('unit')
            ->withPivot('serial_number');
    }

    /**
     * Не придумал как 'sync' 1 товар и много пивот значений, по-этому отзеркалил метод @see units()
     */
    public function unitsBySerial(): BelongsToMany|Product
    {
        return $this->belongsToMany(
            Product::class,
            SystemUnitPivot::TABLE,
            'system_id',
            'serial_number',
            'id'
        )
            ->using(SystemUnitPivot::class);
    }

    public function warrantyRegistration(): HasOne
    {
        return $this->hasOne(WarrantyRegistration::class);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMorphType(): string
    {
        return self::MORPH_NAME;
    }
}
