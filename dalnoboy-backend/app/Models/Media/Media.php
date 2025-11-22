<?php


namespace App\Models\Media;

use Database\Factories\Media\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * @method static MediaFactory factory()
 */
class Media extends BaseMedia
{
    use HasFactory;

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => false,
    ];
}
