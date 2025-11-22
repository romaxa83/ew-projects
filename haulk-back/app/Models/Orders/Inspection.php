<?php

namespace App\Models\Orders;

use App\Models\Files\File;
use App\Models\Files\HasMedia;
use App\Models\Files\InspectionImage;
use App\Models\Files\Traits\HasMediaTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Models\Media;

/**
 * @property int id
 * @property bool has_vin_inspection
 * @property string vin
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string odometer
 * @property string notes
 * @property bool condition_dark
 * @property bool condition_snow
 * @property bool condition_rain
 * @property bool condition_dirty_vehicle
 * @property int num_remotes
 * @property int num_keys
 * @property int num_headrests
 * @property bool drivable
 * @property bool windscreen
 * @property bool glass_all_intact
 * @property bool title
 * @property bool cargo_cover
 * @property bool spare_tire
 * @property bool radio
 * @property bool manuals
 * @property bool navigation_disk
 * @property bool plugin_charger_cable
 * @property bool headphones
 */
class Inspection extends Model implements HasMedia
{
    use HasMediaTrait;
    use HasFactory;

    public const TABLE_NAME = 'inspections';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'vin',
        'condition_dark',
        'condition_snow',
        'condition_rain',
        'condition_dirty_vehicle',
        'odometer',
        'notes',
        'num_keys',
        'num_remotes',
        'num_headrests',
        'drivable',
        'windscreen',
        'glass_all_intact',
        'title',
        'cargo_cover',
        'spare_tire',
        'radio',
        'manuals',
        'navigation_disk',
        'plugin_charger_cable',
        'headphones',
        'damage_labels',
    ];

    protected $casts = [
        'damage_labels' => 'array',
    ];

    public function getImageClass(): string
    {
        return InspectionImage::class;
    }

    public function setCompletedFields(): void
    {
        $this->has_vin_inspection = true;

        if ($this->odometer === null && $this->notes === null) {
            $this->notes = '###';
        }
    }

    /**
     * @return Collection|File[]
     */
    public function getPhotos(): Collection
    {
        return $this->getMediaByWildcard(Order::INSPECTION_PHOTO_COLLECTION_NAME);
    }

    /**
     * @param int $number
     * @param $fileData
     * @param false $clearCollection
     * @param null $metaData
     * @return $this
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addPhoto(int $number, $fileData, bool $clearCollection = false, $metaData = null): self
    {
        $this->addMediaWithRandomName(
            Order::INSPECTION_PHOTO_COLLECTION_NAME . '_' . $number,
            $fileData,
            $clearCollection,
            false,
            $metaData
        );

        return $this;
    }

    public function getPhoto(int $number): ?Media
    {
        return $this->getFirstMedia(Order::INSPECTION_PHOTO_COLLECTION_NAME . '_' . $number);
    }

    public static function decodeDamageLabels(array $labels): array
    {
        $result = [];

        $labelsDescription = config('orders.inspection.damage_labels');

        $labels = array_unique($labels);

        $labels = Arr::sort(
            $labels,
            fn (string $item) => 0 - strlen($labelsDescription[$item])
        );

        foreach ($labels as $label) {
            $result[] = $label . ' - ' . $labelsDescription[$label];
        }

        return $result;
    }
}
