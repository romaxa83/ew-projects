<?php

namespace WezomCms\Promotions\Models;

use DebugBar\DataFormatter\DataFormatter;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;
use WezomCms\Cars\Models\Brand;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\PublishedTrait;
use WezomCms\Core\UseCase\DateFormatter;
use WezomCms\Regions\Models\City;
use WezomCms\Users\Models\User;

/**
 *
 * @property int $id
 * @property bool $published
 * @property int $sort
 * @property int $type
 * @property string|null $code_1c
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 * @mixin PromotionsTranslation
 */
class Promotions extends Model
{
    use Translatable;
    use PublishedTrait;
    use ImageAttachable;
    use Filterable;

    const TYPE_COMMON             = 1;  // общии акции
    const TYPE_INDIVIDUAL         = 2;  // индивидуальные, задаються в 1с
    const TYPE_INDIVIDUAL_FOR_APP = 3;  // индивидуальные, если пользователь не верифицирован в 1с

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'promotions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'published',
        'type',
        'code_1c',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'published' => 'bool',
    ];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = [
        'name',
        'text',
        'link',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    public function isCommon()
    {
        return $this->type == self::TYPE_COMMON;
    }

    public static function getTypeBySelect()
    {
        return [
            self::TYPE_COMMON => __('cms-promotions::admin.Type common'),
            self::TYPE_INDIVIDUAL => __('cms-promotions::admin.Type individual'),
            self::TYPE_INDIVIDUAL_FOR_APP => __('cms-promotions::admin.Type individual for app'),
        ];
    }

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return [
            'image' => 'cms.promotions.promotions.image',
            'image_ua' => 'cms.promotions.promotions.image_ua',
        ];
    }

    public function getImage()
    {
        if(\App::getLocale() == 'uk'){
            return $this->getImageUA();
        }

        return $this->getImageRU();
    }

    public function getImageRU()
    {
        return url($this->getImageUrl(null, 'image', null, false, true));
    }

    public function getImageUA()
    {
        return url($this->getImageUrl(null, 'image_ua', null, false, true));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'promotions_user_relation',
            'promotions_id', 'user_id'
        );
    }
}



