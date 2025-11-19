<?php

declare(strict_types=1);

namespace Wezom\Core\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Wezom\Core\Database\Factories\MediaFactory;
use Wezom\Core\Enums\Images\ImageSizeEnum;

/**
 * \Wezom\Core\Models\Media
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
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
 * @property array $generated_conversions
 * @property array $responsive_images
 * @property int|null $order_column
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $extension
 * @property-read mixed $human_readable_size
 * @property-read Model|Eloquent $model
 * @property-read mixed $original_url
 * @property-read mixed $preview_url
 * @property-read mixed $type
 * @method static MediaCollection<int, static> all($columns = ['*'])
 * @method static MediaFactory factory($count = null, $state = [])
 * @method static MediaCollection<int, static> get($columns = ['*'])
 * @method static Builder<static>|Media newModelQuery()
 * @method static Builder<static>|Media newQuery()
 * @method static Builder<static>|Media ordered()
 * @method static Builder<static>|Media query()
 * @method static Builder<static>|Media whereCollectionName($value)
 * @method static Builder<static>|Media whereConversionsDisk($value)
 * @method static Builder<static>|Media whereCreatedAt($value)
 * @method static Builder<static>|Media whereCustomProperties($value)
 * @method static Builder<static>|Media whereDisk($value)
 * @method static Builder<static>|Media whereFileName($value)
 * @method static Builder<static>|Media whereGeneratedConversions($value)
 * @method static Builder<static>|Media whereId($value)
 * @method static Builder<static>|Media whereManipulations($value)
 * @method static Builder<static>|Media whereMimeType($value)
 * @method static Builder<static>|Media whereModelId($value)
 * @method static Builder<static>|Media whereModelType($value)
 * @method static Builder<static>|Media whereName($value)
 * @method static Builder<static>|Media whereOrderColumn($value)
 * @method static Builder<static>|Media whereResponsiveImages($value)
 * @method static Builder<static>|Media whereSize($value)
 * @method static Builder<static>|Media whereUpdatedAt($value)
 * @method static Builder<static>|Media whereUuid($value)
 * @mixin Eloquent
 */
class Media extends BaseMedia
{
    use HasFactory;

    public function getConversions(): array
    {
        $conversions = [];
        foreach ($this->getGeneratedConversions() as $conversion => $isGenerated) {
            if ($isGenerated) {
                $conversions[] = [
                    'size' => $conversion,
                    'url' => $this->getUrl($conversion),
                ];
            }
        }

        return $conversions;
    }

    public function getSpecialUrl(ImageSizeEnum $size): ?string
    {
        $value = $size->value;

        return $this->hasGeneratedConversion($value)
            ? $this->getFullUrl($value)
            : null;
    }
}
