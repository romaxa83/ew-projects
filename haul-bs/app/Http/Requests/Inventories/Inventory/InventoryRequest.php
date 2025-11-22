<?php

namespace App\Http\Requests\Inventories\Inventory;

use App\Dto\Inventories\InventoryDto;
use App\Enums\Inventories\InventoryPackageType;
use App\Foundations\Enums\DateTimeEnum;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Modules\Seo\Traits\SeoRequestRules;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Unit;
use App\Models\Suppliers\Supplier;
use App\Rules\Inventories\PriceLessMinPrice;
use App\Rules\Inventories\QuantityRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="InventoryRequest",
 *     required={"name", "slug", "stock_number", "unit_id"},
 *     @OA\Property(property="name", type="string", example="Mirror R Ram"),
 *     @OA\Property(property="slug", type="string", example="mirror-r-ram"),
 *     @OA\Property(property="stock_number", type="string", example="ATC-TK-MIRRR"),
 *     @OA\Property(property="article_number", type="string", example="ATC-TK-77789"),
 *     @OA\Property(property="category_id", type="integer", example="12"),
 *     @OA\Property(property="unit_id", type="integer", example="12"),
 *     @OA\Property(property="brand_id", type="integer", example="12"),
 *     @OA\Property(property="supplier_id", type="integer", example="12"),
 *     @OA\Property(property="price_retail", type="number", example="55.44"),
 *     @OA\Property(property="min_limit", type="number", example="3"),
 *     @OA\Property(property="notes", type="string", example="some text"),
 *     @OA\Property(property="for_shop", type="boolean", example="true"),
 *     @OA\Property(property="length", type="number", example="10.0"),
 *     @OA\Property(property="width", type="number", example="13.6"),
 *     @OA\Property(property="height", type="number", example="14"),
 *     @OA\Property(property="weight", type="number", example="44.9"),
 *     @OA\Property(property="package_type", type="string", example="custom_package", enum={"custom_package", "carrier_package"}),
 *     @OA\Property(property="min_limit_price", type="number", example="44.9"),
 *     @OA\Property(property="is_new", type="boolean", example="true"),
 *     @OA\Property(property="is_popular", type="boolean", example="true"),
 *     @OA\Property(property="is_sale", type="boolean", example="true"),
 *     @OA\Property(property="old_price", type="number", example="33.8"),
 *     @OA\Property(property="delivery_cost", type="number", example="3.8"),
 *     @OA\Property(property="discount", type="number", example="5.0"),
 *     @OA\Property(property="main_image", type="string", format="binary", nullable=true , description="The main image for inventory"),
 *     @OA\Property(property="gallery", type="array", description="Gallery images for inventory",
 *         @OA\Items(type="file")
 *     ),
 *     @OA\Property(property="seo", type="object", ref="#/components/schemas/SeoRequest"),
 *     @OA\Property(property="features", type="array", description="Data for features and values",
 *         @OA\Items(ref="#/components/schemas/FeatureForInventoryRequest")
 *     ),
 *     @OA\Property(property="purchase", type="object", ref="#/components/schemas/PurchaseRequest"),
 * )
 *
 * @OA\Schema(schema="FeatureForInventoryRequest", type="object", allOf={
 *      @OA\Schema(
 *          required={"feature_id", "value_ids"},
 *          @OA\Property(property="feature_id", type="integer", description="Feature id", example="1"),
 *          @OA\Property(property="value_id", type="integer", description="Feature value id", example="15"),
 *          @OA\Property(property="value_ids", type="array", description="Ids values this feature", example={1, 22, 3},
 *              @OA\Items(type="integer")
 *          ),
 *     )}
 *  )
 */

class InventoryRequest extends BaseFormRequest
{
    use OnlyValidateForm;
    use SeoRequestRules;

    public function rules(): array
    {
        $id = $this->route('id');

        return array_merge(
            $this->seoRules(),
            [
                'name' => ['required', 'string'],
                'slug' => ['required', 'string',
                    $id
                        ? Rule::unique(Inventory::TABLE, 'slug')->ignore($id)
                        : Rule::unique(Inventory::TABLE, 'slug')
                ],
                'stock_number' => ['required', 'string', 'alpha_dash',
                    $id
                        ? Rule::unique(Inventory::TABLE, 'stock_number')->ignore($id)
                        : Rule::unique(Inventory::TABLE, 'stock_number')
                ],
                'article_number' => ['required', 'string',
                    $id
                        ? Rule::unique(Inventory::TABLE, 'article_number')->ignore($id)
                        : Rule::unique(Inventory::TABLE, 'article_number')
                ],
                'category_id' => ['nullable', 'integer', Rule::exists(Category::TABLE, 'id')],
                'unit_id' => ['required', 'integer', Rule::exists(Unit::TABLE, 'id')],
                'brand_id' => ['nullable', 'integer', Rule::exists(Brand::TABLE, 'id')],
                'supplier_id' => ['nullable', 'integer', Rule::exists(Supplier::TABLE, 'id')],
                'price_retail' => ['bail','nullable', 'numeric', 'min:0.01'],
                'min_limit' => ['required_if:for_shop,true', 'numeric', 'min:0.01'],
//                'min_limit' => ['required_if:for_shop,true', 'numeric', 'min:0', new QuantityRule($this->unit_id ?? null)],
                'notes' => ['nullable', 'string'],
                'for_shop' => ['nullable', 'boolean'],
                'length' => ['required_if:for_shop,true', 'numeric', 'min:0.01'],
                'width' => ['required_if:for_shop,true', 'numeric', 'min:0.01'],
                'height' => ['required_if:for_shop,true', 'numeric', 'min:0.01'],
                'weight' => ['required_if:for_shop,true', 'numeric', 'min:0.01'],
                'package_type' => ['required_if:for_shop,true', 'string', InventoryPackageType::ruleIn()],
                'min_limit_price' => ['bail','nullable', 'numeric', new PriceLessMinPrice($this), 'min:0.01'],
                'is_new' => ['nullable', 'boolean'],
                'is_popular' => ['nullable', 'boolean'],
                'is_sale' => ['nullable', 'boolean'],
                'old_price' => [Rule::requiredIf(to_bool($this->is_sale)), 'numeric', 'min:0.01'],
                'discount' => [Rule::requiredIf(to_bool($this->is_sale)), 'numeric', 'min:0.01'],
                'delivery_cost' => ['nullable', 'numeric', 'min:0.01'],
                Inventory::MAIN_IMAGE_FIELD_NAME => ['nullable', 'image',
                    "max:" . byte_to_kb(config('media-library.max_file_size'))
                ],
                Inventory::GALLERY_FIELD_NAME => ['nullable', 'array'],
                Inventory::GALLERY_FIELD_NAME . '.*' => ['file', 'image', "max:" . byte_to_kb(config('media-library.max_file_size'))],
                'features' => ['nullable', 'array'],
                'features.*.feature_id' => ['required', 'integer', Rule::exists(Feature::TABLE, 'id')],
                'features.*.value_id' => ['required_without:features.*.value_ids', 'numeric', Rule::exists(Value::TABLE, 'id')],
                'features.*.value_ids' => ['required_without:features.*.value_id', 'array', 'min:1'],
                'features.*.value_ids.*' => ['required', 'integer', Rule::exists(Value::TABLE, 'id')]
            ],
            $id
                ? []
                : [
                    'purchase' => ['required', 'array'],
                    'purchase.quantity' => ['required', 'numeric', new QuantityRule($this->unit_id ?? null)],
                    'purchase.date' => ['required', 'string', 'date_format:'. DateTimeEnum::DateSlash->value],
                    'purchase.cost' => ['required', 'numeric', 'min:0.01'],
                    'purchase.invoice_number' => ['nullable', 'string', 'max:15'],
                ]
        );
    }

    public function getDto(): InventoryDto
    {
        return InventoryDto::byArgs($this->validated());
    }
}
