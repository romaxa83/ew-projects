<?php

namespace App\GraphQL\Queries\Common\Catalog\Products;

use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\Catalog\Videos\Links\VideoLinkTypeEnumType;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Videos\VideoLink;
use App\Traits\Technician\IsTechnician;
use Core\GraphQL\Queries\BaseQuery;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseProductQuery extends BaseQuery
{
    use IsTechnician;

    public const NAME = 'product';

    public function args(): array
    {
        return array_merge(
            [
                'id' => [
                    'type' => Type::id(),
                ],
                'video_link_type' => [
                    'type' => VideoLinkTypeEnumType::type(),
                ],
            ],
            $this->getSlugArgs()
        );
    }

    public function type(): Type
    {
        return ProductType::type();
    }

    /**
     * @throws Exception
     */
    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Product
    {
        return $this->getQuery($fields, $args)->firstOrFail()->setBreadcrumbs();
    }

    protected function getQuery(SelectFields $fields, array $args): Product|Builder
    {
        $videoLinkType = $this->getLinkTypes($args);

        return Product::query()
            ->when(isset($args['id']), function (Builder $builder) use ($args) {
                $builder->where(Product::TABLE . '.id', $args['id']);
            })
            ->when(isset($args['slug']), function (Builder $builder) use ($args) {
                $builder->where(Product::TABLE . '.slug', $args['slug']);
            })
            ->select($fields->getSelect() ?: ['id'])
            ->addIsFavourite($this->user())
            ->with($fields->getRelations())
            ->with(['certificates' => fn(BelongsToMany|Certificate $q) => $q
                ->select(Certificate::TABLE . '.*')
                ->addTypeName()
            ])
            ->with(['videoLinks' => fn(BelongsToMany|VideoLink $q) => $q
                    ->when(
                        $videoLinkType ?: false,
                        fn(Builder $b) => $b->whereIn('link_type', $videoLinkType)
                    )
            ])
//            ->with(['manuals' => function($q){
////                dd($this->isCertifiedTechnician());
//                if($this->isCertifiedTechnician()){
//                    $q->whereHas('group', function($q) {
//                        $q->where('show_commercial_certified', false);
//                    });
//                }
//            }])
            ->with('manuals')
            ->with('manuals.group')
            ->groupBy(Product::TABLE . '.id');
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            [
                'id' => ['required_without:slug', 'integer', Rule::exists(Product::TABLE, 'id')],
                'slug' => ['required_without:id', 'string', Rule::exists(Product::TABLE, 'slug')],
            ]
        );
    }

    private function getLinkTypes($args): array
    {
        $tmp = [];
        if(isset($args['video_link_type'])) {
            $tmp[] = $args['video_link_type'];
        }
        if($this->isCertifiedTechnician()){
            $tmp[] = VideoLinkTypeEnum::COMMERCIAL;
        }

        return $tmp;
    }
}
