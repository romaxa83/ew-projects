<?php

namespace WezomCms\Catalog\Models;

use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\ImageMultiUploaderAttachable;
use WezomCms\Core\Traits\Model\OrderBySort;

/**
 * WezomCms\Catalog\Models\ProductImage
 *
 * @property int $id
 * @property string|null $image
 * @property int $default
 * @property int $product_id
 * @property int $sort
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \WezomCms\Catalog\Models\ProductImageTranslation $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\ProductImageTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage listsTranslations($translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage notTranslatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage orWhereTranslation($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage sorting()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage translated()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage translatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage whereTranslation($translationField, $value, $locale = null, $method = 'whereHas', $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage whereTranslationLike($translationField, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductImage withTranslation()
 * @mixin \Eloquent
 * @mixin ProductImageTranslation
 */
class ProductImage extends \Illuminate\Database\Eloquent\Model
{
    use ImageMultiUploaderAttachable;
    use Translatable;
    use OrderBySort;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['default'];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = ['alt', 'title'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translation'];

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.catalog.products.images'];
    }

    /**
     * @return string
     */
    public function getMainColumn(): string
    {
        return 'product_id';
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

    /**
     * @param  Product  $product
     * @param  int|null  $index
     * @return string|null
     */
    public function altAttribute(Product $product, ?int $index = null): ?string
    {
        return $this->alt
            ?: $this->parseTemplate(settings('catalog-seo-templates.image-template.alt', ''), $product, $index);
    }

    /**
     * @param  Product  $product
     * @param  int|null  $index
     * @return string|null
     */
    public function titleAttribute(Product $product, ?int $index = null): ?string
    {
        return $this->title
            ?: $this->parseTemplate(settings('catalog-seo-templates.image-template.title', ''), $product, $index);
    }

    /**
     * @param  string|null  $template
     * @param  Product  $product
     * @param  int|null  $index
     * @return string|null
     */
    protected function parseTemplate(?string $template, Product $product, ?int $index = null): ?string
    {
        $replace = [
            '[name]' => $product->name,
            '[brand]' => optional($product->brand)->name,
            '[category]' => optional($product->category)->name,
            '[cost]' => money($product->cost, true),
        ];

        $result = trim(str_replace(array_keys($replace), array_values($replace), $template));

        if ($index > 1) {
            $result .= ' ' . $index;
        }

        return trim($result);
    }
}
