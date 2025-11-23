<?php

namespace WezomCms\Catalog\Models\Labels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use WezomCms\Catalog\ModelFilters\LabelFilter;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * @property int $id
 * @property bool $published
 * @property bool $is_gender
 * @property int $sort
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin LabelTranslation
 */
class Label extends EloquentModel
{
    use Translatable;
    use HasFactory;
    use PublishedTrait;
    use Filterable;

    public const TABLE = 'labels';
    protected $table = self::TABLE;

    protected $fillable = [
        'published',
        'sort',
    ];

    protected $translatedAttributes = ['name'];

    protected $casts = [
        'published' => 'bool',
        'is_gender' => 'bool'
    ];

    protected $with = ['translation'];

    protected function modelFilter(): string
    {
        return LabelFilter::class;
    }

    public function isGender(): bool
    {
        return $this->is_gender;
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_label_relations',
            'label_id', 'product_id'
        );
    }
}


