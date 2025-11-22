<?php


namespace App\Filters\Branches;


use App\Filters\BaseModelFilter;
use App\Models\Branches\Branch;
use App\Models\Locations\Region;
use App\Models\Locations\RegionTranslate;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Lang;

class BranchFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use LikeRawFilterTrait;
    use SortFilterTrait;

    public function query(string $query): void
    {
        $this
            ->likeRaw('name', $query)
            ->orWhere(
                fn(Builder $builder) => $this->likeRaw('address', $query, $builder)
            )
            ->orWhere(
                fn(Builder $builder) => $this->likeRaw('city', $query, $builder)
            )
            ->orWhereHas(
                'region',
                fn(Builder $builder) => $builder->whereHas(
                    'translates',
                    fn(Builder $translatesBuilder) => $this->likeRaw('title', $query, $translatesBuilder)
                )
            );
    }

    public function name(string $name): void
    {
        $this->whereRaw('lower(name) = ?', [mb_convert_case($name, MB_CASE_LOWER)]);
    }

    public function address(string $address): void
    {
        $this->whereRaw('lower(address) = ?', [mb_convert_case($address, MB_CASE_LOWER)]);
    }

    public function regionId(int $regionId): void
    {
        $this->where('region_id', $regionId);
    }

    public function city(string $city): void
    {
        $this->whereRaw('lower(city) = ?', [mb_convert_case($city, MB_CASE_LOWER)]);
    }

    public function allowedOrders(): array
    {
        return Branch::ALLOWED_ORDERED_FIELDS;
    }

    public function customRegionSort(string $direction): void
    {
        $this->selectRaw(Branch::TABLE . '.*, ' . RegionTranslate::TABLE . '.title')
            ->join(
                Region::TABLE,
                fn(JoinClause $joinClause) => $joinClause->on(
                    Branch::TABLE . '.region_id',
                    '=',
                    Region::TABLE . '.id'
                )
                    ->join(
                        RegionTranslate::TABLE,
                        fn(JoinClause $clause) => $clause->on(
                            Region::TABLE . '.id',
                            '=',
                            RegionTranslate::TABLE . '.row_id'
                        )
                            ->where(RegionTranslate::TABLE . '.language', '=', Lang::getLocale())
                            ->orderBy('title', $direction)
                    )
            );
    }
}
