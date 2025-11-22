<?php

namespace App\GraphQL\Queries\FrontOffice\Favourites;

use App\GraphQL\Types\Catalog\Favourites\FavouriteProductType;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Favourites\Favourite;
use App\Models\Catalog\Products\Product;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class FavouriteProductsQuery extends BaseQuery
{
    public const NAME = 'favouriteProducts';

    public function __construct()
    {
        $this->setMemberGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck();
    }

    public function args(): array
    {
        return array_merge(
            [
                'id' => [
                    'name' => 'id',
                    'type' => Type::id()
                ],
            ],
            parent::args()
        );
    }

    public function type(): Type
    {
        return FavouriteProductType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            $this->getQuery(),
            $args,
        );
    }

    protected function getQuery(): Favourite|Builder|MorphMany
    {
        return $this->user()
            ->favourites()
            ->where('favorable_type', Product::MORPH_NAME)
            ->whereHas('favorable', fn(Builder $b) => $b->where('active', true))
            ->with(
                [
                    'favorable' => fn(MorphTo $b) => $b->morphWith(
                        [
                            Product::class => [
                                'certificates' => fn(BelongsToMany|Certificate $q) => $q
                                    ->select(Certificate::TABLE . '.*')
                                    ->addTypeName(),
                                'videoLinks.group',
                                'manuals.group',
                                'translation',
                            ]
                        ]
                    )
                ]
            );
    }
}
