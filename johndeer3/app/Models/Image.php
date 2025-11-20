<?php

namespace App\Models;

use App\Helpers\ImageExifData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Image
 *
 * @property int $id
 * @property string $model
 * @property string $entity_type
 * @property int $entity_id
 * @property string $url
 * @property string $basename
 * @property string $lat
 * @property string $lon
 * @property string $photo_created_at
 * @property string $metadata
 */

class Image extends Model
{
    use HasFactory;

    const WORKING_START = 'working_hours_at_the_beg';
    const WORKING_END = 'working_hours_at_the_end';
    const EQUIPMENT = 'equipment_on_the_field';
    const ME = 'me_and_equipment';
    const OTHERS = 'others';
    const SIGNATURE = 'signature';

    protected $table = 'images';

    protected $casts = [
        'metadata' => 'array'
    ];

    public static function create($key, $entity, $entityId, $url, $basename, $file = null)
    {
        $image = new self();
        $image->model = $key;
        $image->entity_type = $entity;
        $image->entity_id = $entityId;
        $image->url = $url;
        $image->basename = $basename;

        if($file){
            $metadata = new ImageExifData($file);
            $image->lat = $metadata->getLat();
            $image->lon = $metadata->getLon();
            if($metadata->getMetaData() != null){
                $image->metadata = mb_convert_encoding($metadata->getMetaData(), 'UTF-8', 'UTF-8');
            }
            $image->photo_created_at = $metadata->getDateCreatePhoto();
        }

        $image->save();
    }

    public static function getUrl($url)
    {
        if(!$url){
            return $url;
        }

        return config('app.url') . '/storage/'. $url;
    }

    public function getCoords()
    {
        if($this->lat && $this->lon){
            return [
                'lat' => $this->lat,
                'lon' => $this->lon,
            ];
        }

        return null;
    }

    public function isSignature(): bool
    {
        return $this->model == self::SIGNATURE;
    }

    public static function formatArray(array $images): array
    {
        $array = [];

        foreach($images as $image){
            if($image['model'] == self::WORKING_START){
                $array[self::WORKING_START][] = $image;
            }
            if($image['model'] == self::WORKING_END){
                $array[self::WORKING_END][] = $image;
            }
            if($image['model'] == self::EQUIPMENT){
                $array[self::EQUIPMENT][] = $image;
            }
            if($image['model'] == self::ME){
                $array[self::ME][] = $image;
            }
            if($image['model'] == self::OTHERS){
                $array[self::OTHERS][] = $image;
            }
            if($image['model'] == self::SIGNATURE){
                $array[self::SIGNATURE][] = $image;
            }
        }

        return $array;
    }

    public function entity()
    {
        return $this->morphTo();
    }
}
