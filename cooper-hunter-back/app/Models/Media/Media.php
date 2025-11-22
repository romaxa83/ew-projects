<?php

namespace App\Models\Media;

use Carbon\Carbon;
use Database\Factories\Media\MediaFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * Class Media
 * @package App\Models\Media
 *
 * @property int $id
 * @property string $model_type
 * @property string $model_id
 * @property string|null $uuid
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string|null $mime_type
 * @property string $disk
 * @property string|null $conversions_disk
 * @property int $size
 * @property array $manipulations
 * @property array $custom_properties
 * @property array $generate_conversions
 * @property array $responsive_images
 * @property int|null $order_column
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $sort
 *
 * @method static MediaFactory factory(...$options)
 */
class Media extends SpatieMedia
{
    public const TABLE = 'media';

    protected $fillable = [
        'sort'
    ];
}
