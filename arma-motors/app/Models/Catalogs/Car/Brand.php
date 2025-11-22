<?php

namespace App\Models\Catalogs\Car;

use App\Casts\MoneyCast;
use App\Casts\UuidCast;
use App\Models\BaseModel;
use App\Models\Catalogs\Calc\Mileage;
use App\Models\Catalogs\Calc\SparesGroup;
use App\Models\Catalogs\Calc\Work;
use App\Models\Dealership\Dealership;
use App\Traits\Media\ImageRelation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Media\Image;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property string $uuid
 * @property bool $active
 * @property bool $is_main
 * @property int $sort
 * @property int $color
 * @property string $name
 * @property int $hourly_payment
 * @property int $discount_hourly_payment
 *
 */
class Brand extends BaseModel
{
    use ImageRelation;

    public const COLOR_NONE   = 0;
    public const COLOR_RED    = 1;
    public const COLOR_YELLOW = 2;
    public const COLOR_BLUE   = 3;

    public const VOLVO      = 'volvo';
    public const RENAULT    = 'renault';
    public const MITSUBISHI = 'mitsubishi';

    public $timestamps = false;

    public const TABLE = 'car_brands';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool',
        'is_main' => 'bool',
        'uuid' => UuidCast::class,
        'hourly_payment' => MoneyCast::class,
        'discount_hourly_payment' => MoneyCast::class,
    ];

    public static function colors(): array
    {
        return [
            self::COLOR_NONE => __('translation.brand.color.none'),
            self::COLOR_RED => __('translation.brand.color.red'),
            self::COLOR_YELLOW => __('translation.brand.color.yellow'),
            self::COLOR_BLUE => __('translation.brand.color.blue')
        ];
    }

    public function checkColor($color): bool
    {
        return array_key_exists($color, self::colors());
    }

    public function assetColor($color): void
    {
        if(!$this->checkColor($color)){
            throw new \InvalidArgumentException(__('error.brand.not defined color', ['color' => $color]));
        }
    }

    public static function isMainFromName(string $name): bool
    {
        $target = ['volvo', 'renault', 'mitsubishi'];

        return in_array(strtolower($name), $target);
    }

    public function isMain(): bool
    {
        return $this->is_main;
    }

    public function isVolvo(): bool
    {
        return strtolower($this->name) === self::VOLVO;
    }

    public function isRenault(): bool
    {
        return strtolower($this->name) === self::RENAULT;
    }

    public function isMitsubishi(): bool
    {
        return strtolower($this->name) === self::MITSUBISHI;
    }

    // relation

    public function dealership(): HasOne
    {
        return $this->hasOne(Dealership::class);
    }

    public function models(): HasMany
    {
        return $this->hasMany(Model::class);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'entity');
    }

    public function mileages(): BelongsToMany
    {
        return $this->belongsToMany(
            Mileage::class,
            'car_brand_mileage_relations',
            'brand_id', 'mileage_id'
        );
    }

    public function works(): BelongsToMany
    {
        return $this->belongsToMany(
            Work::class,
            'car_brand_work_relations',
            'brand_id', 'work_id'
        );
    }

    public function sparesGroups()
    {
        return $this->hasMany(SparesGroup::class, 'brand_id', 'id');
    }
}

