<?php

namespace WezomCms\Dealerships\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\ImageMultiUploaderAttachable;

/**
 *
 * @property int $id
 * @property string|null $image
 * @property int $default
 * @property int $dealership_id
 * @property int $sort
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class DealershipImages extends Model
{
    use ImageMultiUploaderAttachable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['default'];


    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.dealerships.dealerships.images'];
    }

    /**
     * @return string
     */
    public function getMainColumn(): string
    {
        return 'dealership_id';
    }

    /**
     * Determines the presence of the "name" field in the database.
     *
     * @return bool
     */
    public function hasNameField()
    {
        return false;
    }

    public function getImage($size = null)
    {
//        dd($this->getOriginal());
        return url($this->getImageUrl($size, 'image'));
    }
}


