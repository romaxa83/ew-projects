<?php

namespace App\Models\News;

use App\Models\Files\HasMedia;
use App\Models\Files\NewsImage;
use App\Models\Files\Traits\HasMediaTrait;
use App\ModelFilters\News\NewsFilter;
use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class News extends Model implements HasMedia
{
    use Filterable;
    use HasMediaTrait;
    use SetCompanyId;

    public const TABLE_NAME = 'news';

    public const NEWS_PHOTO_FIELD_NAME = 'image_file';
    public const NEWS_PHOTO_COLLECTION_NAME = 'news_images';

    private const DEFAULT_LANG = 'en';

    /**
     * @var array
     */
    protected $fillable = [
        'title_en',
        'title_ru',
        'title_es',
        'body_short_en',
        'body_short_ru',
        'body_short_es',
        'body_en',
        'body_ru',
        'body_es',
        'sticky',
        'status',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

    public function modelFilter(): string
    {
        return NewsFilter::class;
    }

    public function getImageClass(): string
    {
        return NewsImage::class;
    }

    public function getTitle($lang)
    {
        if ($lang && $this->{'title_' . $lang}) {
            return $this->{'title_' . $lang};
        }

        return $this->{'title_' . self::DEFAULT_LANG};
    }

    public function getBodyShort($lang)
    {
        if ($lang && $this->{'body_short_' . $lang}) {
            return $this->{'body_short_' . $lang};
        }

        return $this->{'body_short_' . self::DEFAULT_LANG};
    }

    public function getBody($lang)
    {
        if ($lang && $this->{'body_' . $lang}) {
            return $this->{'body_' . $lang};
        }

        return $this->{'body_' . self::DEFAULT_LANG};
    }
}
