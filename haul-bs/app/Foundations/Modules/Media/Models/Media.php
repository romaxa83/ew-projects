<?php

namespace App\Foundations\Modules\Media\Models;

use Carbon\CarbonImmutable;
use Database\Factories\Media\MediaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * @property int id
 * @property string model_type
 * @property string model_id
 * @property string|null $uuid
 * @property string collection_name
 * @property string name
 * @property string file_name
 * @property string|null mime_type
 * @property string disk
 * @property string|null conversions_disk
 * @property int size
 * @property array manipulations
 * @property array custom_properties
 * @property array generate_conversions
 * @property array responsive_images
 * @property int sort
 * @property boolean is_main
 * @property int|null origin_id
 * @property CarbonImmutable|null created_at
 * @property CarbonImmutable|null updated_at
 *
 * @method static MediaFactory factory(...$options)
 */
class Media extends SpatieMedia
{
    use HasFactory;

    public const TABLE = 'media';

    protected $fillable = [
        'sort',
        'model_type',
        'model_id',
        'collection_name',
        'name',
        'file_name',
        'is_main',
    ];

    protected static function newFactory(): Factory
    {
        return MediaFactory::new();
    }
}
