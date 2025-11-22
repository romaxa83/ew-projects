<?php

namespace App\Filters\Catalog;

use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\UnitType;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SlugFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class ProductFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;
    use SlugFilterTrait;

    private const RELATIVE_CATEGORY_SINGLE = 1;
    private const RELATIVE_CATEGORY_MULTI = 2;

    /**
     * Договор с клиентом был такой, что товар должен отображаться только в конкретной связанной категорией.
     * Теперь он захотел что бы товары тянулись со всех вложенных связанных категорий.
     *
     * Так что изначально по тз:
     * RELATIVE_CATEGORY_STRATEGY = self::RELATIVE_CATEGORY_SINGLE
     */
    private const RELATIVE_CATEGORY_STRATEGY = self::RELATIVE_CATEGORY_MULTI;

    public function searchQuery(string $query): void
    {
        $this->query($query);
    }

    public function query(string $query): void
    {
        $query = mb_convert_case($query, MB_CASE_LOWER);
        $titleSearch = makeSearchSlug($query);

        $this->where(
            function (Builder $builder) use ($query, $titleSearch) {
                $builder->whereRaw('LOWER(`title`) LIKE ?', ["%$query%"]);
                $builder->orWhereRaw(
                    'LOWER(`title_metaphone`) LIKE ?',
                    ["%$titleSearch%"]
                )
                    ->orWhereHas(
                        'serialNumbers',
                        fn(Builder $builder) => $builder->whereRaw(
                            "LOWER(`serial_number`) LIKE ?",
                            ['%' . $query . '%']
                        )
                    )
                    ->orWhereHas(
                        'keywords',
                        fn(Builder $builder) => $builder->whereRaw(
                            "LOWER(`keyword`) LIKE ?",
                            ['%' . $query . '%']
                        )
                    );
            }
        );
    }

    public function title(string $title): Builder|ProductFilter
    {
        $title = strtolower($title);
        $titleSearch = makeSearchSlug($title);

        return $this->where(
            function (Builder $builder) use ($title, $titleSearch) {
                $builder->orWhereRaw('LOWER(`title`) LIKE ?', ["%$title%"]);
                $builder->orWhereRaw(
                    'LOWER(`title_metaphone`) LIKE ?',
                    ["%$titleSearch%"]
                );
            }
        );
    }

    public function active(bool $active): void
    {
        $this->where(Product::TABLE . '.active', $active);
    }

    public function categorySlug(string $slug): void
    {
        $categoryId = Category::whereSlug($slug)
            ->first();

        if (!$categoryId) {
            return;
        }

        $this->category($categoryId);
    }

    /**
     * @param $categoryId
     *
     * @throws Exception
     */
    public function category($categoryId): void
    {
        $ids = categoryStorage()->getAllChildrenIds($categoryId);

        $relative = match (self::RELATIVE_CATEGORY_STRATEGY) {
            self::RELATIVE_CATEGORY_MULTI => $ids,
            default => is_object($categoryId)
                ? $categoryId->id
                : $categoryId,
        };

        $relative = Arr::wrap($relative);

        $this->where(
            static fn(Builder $q) => $q
                ->whereIn('category_id', $ids)
                ->orWhereHas(
                    'relativeCategories',
                    static fn(Builder $b) => $b->whereIn('category_id', $relative)
                )
        );
    }

    public function categoryType(string $type): void
    {
        $categoryId = Category::whereType($type)
            ->first();

        if (!$categoryId) {
            return;
        }

        $this->category($categoryId);
    }

    public function unitType(string $type): void
    {
        $unitType = UnitType::query()->where('name', $type)->first();

        if (!$unitType) {
            return;
        }

        $this->where('unit_type_id', $unitType->id);
    }

    public function valueIds(array $ids): void
    {
        $values = Value::query()
            ->whereKey($ids)
            ->select('id', 'feature_id')
            ->getQuery()
            ->get();

        $valuesMap = [];

        foreach ($values as $value) {
            $valuesMap[$value->feature_id][] = $value->id;
        }

        $this->where(
            static function (Builder $query) use ($valuesMap) {
                foreach ($valuesMap as $featureId => $valueIds) {
                    $query->whereHas(
                        'values',
                        fn(Builder $valuesBuilder) => $valuesBuilder->where(
                            'feature_id',
                            $featureId
                        )->whereIn(
                            'id',
                            $valueIds
                        )
                    );
                }
            }
        );
    }

    public function serialNumber(string $serialNumber): void
    {
        $this->whereHas(
            'serialNumbers',
            static fn(HasMany $b) => $b->whereRaw(
                'LOWER(`serial_number`) LIKE ?',
                ["%$serialNumber%"]
            )
        );
    }

    protected function allowedOrders(): array
    {
        return Product::ALLOWED_SORTING_FIELDS;
    }
}
