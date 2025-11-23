<?php

namespace WezomCms\Catalog\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Models\ProductImage;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Traits\Model\ImageAttachable;

/**
 * Trait ProductImageTrait
 * @package WezomCms\Catalog\Traits
 * @mixin Product
 */
trait ProductImageTrait
{
    use ImageAttachable {
        ImageAttachable::imageExists as protected traitImageExists;
        ImageAttachable::getImageUrl as protected traitGetImageUrl;
    }

    /**
     * Images configuration.
     *
     * @return array
     */
    public function imageSettings(): array
    {
        return [];
    }

    /**
     * @return HasMany
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('default')->sorting();
    }

    /**
     * @return HasMany
     */
    public function mainImage()
    {
        return $this->hasMany(ProductImage::class)->where('default', true);
    }

    /**
     * @param  string|null  $size
     * @param  string  $field
     * @return string|null
     */
    public function getImageUrl(string $size = null, string $field = 'image')
    {
        if (in_array($field, array_keys($this->imageSettings()))) {
            return static::traitGetImageUrl($size, $field);
        }

        $image = $this->mainImage->first() ?: new ProductImage();

        return $image->getImageUrl($size, $field);
    }

    /**
     * @param  string|null  $size
     * @param  string|null  $field
     * @return bool
     */
    public function imageExists(string $size = null, string $field = 'image')
    {
        if (in_array($field, array_keys($this->imageSettings()))) {
            return static::traitImageExists($size, $field);
        }

        $image = $this->mainImage->first();

        return $image ? $image->imageExists($size, $field) : false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function deleteAllImages(): bool
    {
        if ($this->isForceDeleting() === false) {
            $this->images()
                ->skip(1)
                ->take(PHP_INT_MAX)
                ->get()
                ->each(function (ProductImage $image) {
                    $image->delete();
                });
        } else {
            $this->images->each->delete();
        }

        return true;
    }

    /**
     * @param  string|null  $size
     * @param  string  $relation
     * @return Collection
     */
    public function getExistImages(string $size = null, string $relation = 'images'): Collection
    {
        return $this->loadMissing($relation)
            ->getRelation($relation)
            ->filter(function ($image) use ($size) {
                /** @var $image ImageAttachable */
                return $image->imageExists($size);
            });
    }

    /**
     * @return string|null
     */
    public function getImageAltAttribute(): ?string
    {
        if ($this->relationLoaded('images')) {
            $image = $this->images->first();
        } else {
            $image = $this->mainImage->first();
        }
        if (empty($image)) {
            $image = new ProductImage();
        }

        return $image->altAttribute($this);
    }

    /**
     * @return string|null
     */
    public function getImageTitleAttribute(): ?string
    {
        if ($this->relationLoaded('images')) {
            $image = $this->images->first();
        } else {
            $image = $this->mainImage->first();
        }
        if (empty($image)) {
            $image = new ProductImage();
        }

        return $image->titleAttribute($this);
    }

    /**
     * @return Collection|ProductImage[]|string[]
     */
    public function getGalleryAttribute()
    {
        $this->loadMissing('images');

        $images = $this->images->filter(function (ProductImage $image) {
            return $image->imageExists('big');
        });

        $videos = array_filter($this->videos, function ($video) {
            return Helpers::getYoutubeId($video);
        });

        foreach ($videos as $video) {
            $images->push($video);
        }

        return $images;
    }
}
