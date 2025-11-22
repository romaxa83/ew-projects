<?php

namespace App\GraphQL\Types\Catalog\Products;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Brands\BrandType;
use App\GraphQL\Types\Catalog\Categories\CategoryBreadcrumbType;
use App\GraphQL\Types\Catalog\Categories\CategoryType;
use App\GraphQL\Types\Catalog\Categories\SimpleCategoryType;
use App\GraphQL\Types\Catalog\Certificates\CertificateType;
use App\GraphQL\Types\Catalog\Features\Specifications\SpecificationType;
use App\GraphQL\Types\Catalog\Features\Values\ValueType;
use App\GraphQL\Types\Catalog\Labels\LabelType;
use App\GraphQL\Types\Catalog\Manuals\ManualGroupType;
use App\GraphQL\Types\Catalog\Manuals\ManualType;
use App\GraphQL\Types\Catalog\Solutions\SolutionType;
use App\GraphQL\Types\Catalog\Troubleshoots\Groups\TroubleshootGroupType;
use App\GraphQL\Types\Catalog\Videos\Groups\VideoGroupType;
use App\GraphQL\Types\Catalog\Videos\Links\VideoLinkType;
use App\GraphQL\Types\Enums\Catalog\Products\ProductOwnerTypeEnumType;
use App\GraphQL\Types\Enums\Catalog\Products\ProductUnitSubTypeEnumType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Videos\Group;
use App\Services\Catalog\ProductService;
use App\Traits\GraphQL\HasGuidTrait;
use App\Traits\Technician\IsTechnician;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

class ProductType extends BaseType
{
    use HasGuidTrait;
    use IsTechnician;

    public const NAME = 'ProductType';
    public const MODEL = Product::class;

    public function __construct(private ProductService $service)
    {
    }

    public function fields(): array
    {
        $fields = [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'breadcrumbs' => [
                'type' => CategoryBreadcrumbType::list(),
                'description' => 'Breadcrumbs are allowed only on single product page',
                'selectable' => false,
                'is_relation' => false,
                'always' => 'category_id'
            ],
            'keywords' => [
                'type' => ProductKeywordType::list(),
            ],
            'is_favourite' => [
                'type' => Type::boolean(),
                'selectable' => false,
                'resolve' => fn(Product $p) => (bool)$p->is_favourite
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'title' => [
                'type' => NonNullType::string()
            ],
            'seer' => [
                'type' => Type::float(),
            ],
            'slug' => [
                'type' => NonNullType::string()
            ],
            'category' => [
                'type' => CategoryType::type(),
                'is_relation' => true,
            ],
            'unitType' => [
                'type' => UnitTypeType::type(),
                'is_relation' => true,
            ],
            'show_rebate' => [
                'type' => NonNullType::boolean(),
            ],
            'relative_categories' => [
                'type' => SimpleCategoryType::nonNullList(),
                'is_relation' => true,
                'alias' => 'relativeCategories',
            ],
            'video_links' => [
                'type' => VideoLinkType::list(),
                'is_relation' => true,
                'alias' => 'videoLinks',
            ],
            'video_groups' => [
                /** @see ProductType::resolveVideoGroupsField() */
                'type' => VideoGroupType::list(),
                'is_relation' => false,
                'selectable' => false,
            ],
            'troubleshoot_groups' => [
                'type' => TroubleshootGroupType::list(),
                'is_relation' => true,
                'alias' => 'troubleshootGroups'
            ],
            'relations' => [
                'type' => self::list(),
                'is_relation' => true,
                'alias' => 'relationProducts'
            ],
            'labels' => [
                'type' => LabelType::list(),
                'is_relation' => true,
            ],
            'specifications' => [
                'type' => SpecificationType::list(),
                'is_relation' => true,
            ],
            'images' => [
                'type' => MediaType::list(),
                'always' => 'id',
                'alias' => 'media',
            ],
            'values' => [
                'type' => ValueType::list(),
                'query' => fn(array $args, $query, $ctx) => $query
                    ->join(
                        Feature::TABLE . ' as feature_sorting', fn(JoinClause $j) => $j
                        ->on(
                            'feature_sorting.id',
                            '=',
                            Value::TABLE . '.feature_id'
                        )
                    )
                    ->latest('feature_sorting.sort'),
            ],
            'mobile_values' => [
                'type' => ValueType::list(),
                'alias' => 'mobileValues'
            ],
            'web_values' => [
                'type' => ValueType::list(),
                'alias' => 'webValues'
            ],
            'certificates' => [
                'type' => CertificateType::list(),
            ],
            'manuals' => [
                /** @see ProductType::resolveManualsField() */
                'type' => ManualType::list(),
                'is_relation' => false,
                'selectable' => false,
            ],
            'manual_groups' => [
                /** @see ProductType::resolveManualGroupsField() */
                'type' => ManualGroupType::list(),
                'is_relation' => false,
                'selectable' => false,
            ],
            'solution' => [
                'type' => SolutionType::type(),
                'is_relation' => true,
                'description' => 'Setting for "Find Solution" page',
            ],
            'translation' => [
                'type' => TranslateType::nonNullType(),
                'is_relation' => true,
            ],
            'translations' => [
                'type' => TranslateType::nonNullList(),
                'is_relation' => true,
            ],
            'similar_products' => [
                'type' => self::list(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(Product $product) => $this->service->getSimilarProducts($product)
            ],
            'brand' => [
                'type' => BrandType::type()
            ],
            'owner_type' => [
                'type' => ProductOwnerTypeEnumType::nonNullType()
            ],
            'img' => [
                'type' => Type::string(),
                'is_relation' => false,
                'selectable' => false,
                'always' => 'olmo_additions',
                'resolve' => fn(Product $product) => $product->getImgUrl()
            ],
            'unit_sub_type' => [
                'type' => ProductUnitSubTypeEnumType::type()
            ],
        ];

        return array_merge(
            parent::fields(),
            $this->getGuidField(),
            $fields
        );
    }

    protected function resolveVideoGroupsField(Product $p): Collection
    {
        if (!$p->relationLoaded('videoLinks')) {
            return collect();
        }

        $groups = collect();

        /** @var Group $group */
        foreach ($p->videoLinks as $videoLink) {
            $group = $videoLink->group;

            $group->setRelation('links', $p->videoLinks->where('group_id', $group->id));
            $groups->put($group->getKey(), $group);
        }

        return $groups;
    }

    protected function resolveManualGroupsField(Product $p): Collection
    {
        if (!$p->relationLoaded('manuals')) {
            return collect();
        }

        $groups = collect();

        /** @var ManualGroup $group */
        foreach ($p->manuals as $manual) {
            $group = $manual->group;

            if((!$this->isCertifiedTechnician()) && $group->show_commercial_certified == true){
                continue;
            }

            $group->setRelation('manuals', $p->manuals->where('manual_group_id', $group->id));
            $groups->put($group->getKey(), $group);
        }

        return $groups;
    }

    protected function resolveManualsField(Product $p): Collection
    {
//        if (!$p->relationLoaded('manuals')) {
//            return collect();
//        }

        $items = collect();

        /** @var Manual $manual */
        foreach ($p->manuals as $manual) {

            if((!$this->isCertifiedTechnician()) && $manual->group->show_commercial_certified == true){
                continue;
            }

            $items->put($manual->getKey(), $manual);
        }

        return $items;
    }
}
