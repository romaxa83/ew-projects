<?php

namespace WezomCms\Firebase\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * @property int $id
 * @property array $vars
 * @property string $type
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 * @mixin TemplateTranslation
 */

class Template extends Model
{
    use Translatable;
    use PublishedTrait;

    public const TYPE_TEST = 'test';
    public const TYPE_REGISTRY = 'registry';
    public const TYPE_COLLECTION_SOON_FINISH = 'collection_soon_finish';
    public const TYPE_COLLECTION_START = 'collection_start';
    public const TYPE_ORDER_CHANGE_STATUS = 'orders_status_changed';

    public const TABLE = 'fcm_templates';

    protected $table = self::TABLE;

    protected $fillable = ['active'];

    protected $translatedAttributes = ['title', 'text'];

    protected $with = ['translation'];

    protected $casts = [
        'vars' => 'array',
        'active' => 'boolean',
    ];

    public function publishedField(): string
    {
        return 'active';
    }

    public static function listType(): array
    {
        return [
            self::TYPE_TEST => __('cms-firebase::admin.type.test'),
            self::TYPE_REGISTRY => __('cms-firebase::admin.type.registry'),
            self::TYPE_COLLECTION_SOON_FINISH => __('cms-firebase::admin.type.collection_soon_finish'),
            self::TYPE_COLLECTION_START => __('cms-firebase::admin.type.collection_start'),
            self::TYPE_ORDER_CHANGE_STATUS => __('cms-firebase::admin.type.orders_status_changed'),
        ];
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
