<?php

namespace WezomCms\Core\Traits\Model;

use Cviebrock\EloquentSluggable\Services\SlugService;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Str;

trait MultiLanguageSluggableTrait
{
    use Sluggable;

    /**
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    /**
     * @param $value
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = SlugService::createSlug($this, 'slug', (string) $value);
    }

    /**
     * Query scope for finding "similar" slugs, used to determine uniqueness.
     *
     * @param  Builder  $query
     * @param  string  $attribute
     * @param  array  $config
     * @param  string  $slug
     * @return Builder
     */
    public function scopeFindSimilarSlugs(Builder $query, $attribute, $config, $slug)
    {
        $separator = $config['separator'];

        if ($primaryField = $this->primaryFieldName()) {
            $query->where($primaryField, '!=', $this->getAttribute($primaryField));
        }

        return $query->where('locale', $this->getAttribute('locale'))
            ->where($attribute, '=', $slug)
            ->orWhere($attribute, 'LIKE', $slug . $separator . '%');
    }

    /**
     * Specifies the name of the main field.
     *
     * @return string
     */
    protected function primaryFieldName(): string
    {
        $mainTableName = str_replace('_translations', '', $this->getTable());

        return Str::snake(Str::singular($mainTableName)) . '_id';
    }
}
