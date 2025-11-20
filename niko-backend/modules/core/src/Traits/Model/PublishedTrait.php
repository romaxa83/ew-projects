<?php

namespace WezomCms\Core\Traits\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait PublishedTrait
 * @package WezomCms\Core\Traits\Model
 * @method Builder|Model published()
 * @method Builder|Model publishedWithSlug($slug, $slugField = 'slug')
 */
trait PublishedTrait
{
    /**
     * Filter result.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopePublished($query)
    {
        $field = 'published';

        if (method_exists($this, 'publishedField')) {
            $field = $this->publishedField();
        }

        // If multilingual
        if (method_exists($this, 'translate') && $this->isTranslationAttribute($field)) {
            $query->whereTranslation($field, true, \App::getLocale());
        } else {
            $query->where($field, true);
        }

        if (method_exists($this, 'filterPublished')) {
            $this->filterPublished($query);
        }

        return $query;
    }

    /**
     * @param  Builder  $query
     * @param $slug
     * @param  string  $slugField
     * @return Builder
     */
    public function scopePublishedWithSlug($query, $slug, $slugField = 'slug')
    {
        $field = 'published';

        if (method_exists($this, 'publishedField')) {
            $field = $this->publishedField();
        }

        // If multilingual
        if (method_exists($this, 'translate')) {
            $multilingualFields = [];
            $baseFields = [];
            foreach ([$field => true, $slugField => $slug] as $key => $value) {
                if ($this->isTranslationAttribute($key)) {
                    $multilingualFields[$key] = $value;
                } else {
                    $baseFields[$key] = $value;
                }
            }
            if (!empty($multilingualFields)) {
                $query->whereHas('translations', function ($subQuery) use ($multilingualFields) {
                    /** @var Builder $subQuery */
                    $subQuery->where('locale', \App::getLocale())
                        ->where($multilingualFields);
                });
            }
            if (!empty($baseFields)) {
                $query->where($baseFields);
            }
        } else {
            $query->where($field, true)->where($slugField, $slug);
        }

        if (method_exists($this, 'filterPublished')) {
            $this->filterPublished($query);
        }

        return $query;
    }
}
