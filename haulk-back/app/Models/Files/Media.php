<?php

namespace App\Models\Files;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Models\Files\File
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string|null $mime_type
 * @property string $disk
 * @property int $size
 * @property array $manipulations
 * @property array $custom_properties
 * @property array $responsive_images
 * @property int|null $order_column
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $extension
 * @property-read mixed $human_readable_size
 * @property-read mixed $type
 * @property-read File $model
 * @method static Builder|static newModelQuery()
 * @method static Builder|static newQuery()
 * @method static Builder|static ordered()
 * @method static Builder|static query()
 * @method static Builder|static whereCollectionName($value)
 * @method static Builder|static whereCreatedAt($value)
 * @method static Builder|static whereCustomProperties($value)
 * @method static Builder|static whereDisk($value)
 * @method static Builder|static wherestaticName($value)
 * @method static Builder|static whereId($value)
 * @method static Builder|static whereManipulations($value)
 * @method static Builder|static whereMimeType($value)
 * @method static Builder|static whereModelId($value)
 * @method static Builder|static whereModelType($value)
 * @method static Builder|static whereName($value)
 * @method static Builder|static whereOrderColumn($value)
 * @method static Builder|static whereResponsiveImages($value)
 * @method static Builder|static whereSize($value)
 * @method static Builder|static whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Media extends \Spatie\MediaLibrary\Models\Media
{
    public const TABLE_NAME = 'media';

    protected $table = self::TABLE_NAME;

    public array $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => false,
    ];
}
