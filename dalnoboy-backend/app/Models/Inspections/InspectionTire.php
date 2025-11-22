<?php

namespace App\Models\Inspections;

use App\Contracts\Models\HasModeration;
use App\Models\BaseModel;
use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\Recommendation;
use App\Models\Tires\Tire;
use App\Models\Vehicles\Schemas\SchemaWheel;
use App\Traits\HasFactory;
use App\Traits\Model\InteractsWithMedia;
use App\Traits\Model\ModeratedScopeTrait;
use App\Traits\Model\RuleInTrait;
use Database\Factories\Inspections\InspectionTireFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;

/**
 * @method static InspectionTireFactory factory()
 */
class InspectionTire extends BaseModel implements HasMedia, HasModeration
{
    use HasFactory;
    use InteractsWithMedia;
    use ModeratedScopeTrait;
    use RuleInTrait;

    public const PHOTO_MAIN = 'main';
    public const PHOTO_SERIAL_NUMBER = 'serial_number';
    public const MEDIA_COLLECTION_NAME = 'inspection_tires';

    public $timestamps = false;

    protected $fillable = [
        'inspection_id',
        'tire_id',
        'schema_wheel_id',
        'ogp',
        'pressure',
        'no_problems',
        'comment',
    ];

    protected $casts = [
        'ogp' => 'float',
        'pressure' => 'float',
        'no_problems' => 'bool',
    ];

    public function getMediaCollectionName(): string
    {
        return self::MEDIA_COLLECTION_NAME;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(array_merge(
                $this->mimeImage()
            ));
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id', 'id');
    }

    public function tire(): BelongsTo
    {
        return $this->belongsTo(Tire::class, 'tire_id', 'id');
    }

    public function schemaWheel(): BelongsTo
    {
        return $this->belongsTo(SchemaWheel::class, 'schema_wheel_id', 'id');
    }

    public function problems(): BelongsToMany
    {
        return $this
            ->belongsToMany(Problem::class)
            ->using(InspectionTireProblem::class);
    }

    public function recommendations(): BelongsToMany
    {
        return $this->belongsToMany(Recommendation::class)
            ->using(InspectionTireRecommendation::class)
            ->withPivot('is_confirmed');
    }

    public function isModerated(): bool
    {
        return true;
    }

    public function shouldModerated(): bool
    {
        return $this->tire->shouldModerated();
    }

    public function previousTireInspection(): ?self
    {
        return $this->tire->tireInspections()->where('inspection_id', '<', $this->inspection->id)->first();
    }
}
