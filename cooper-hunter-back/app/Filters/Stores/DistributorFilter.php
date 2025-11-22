<?php

declare(strict_types=1);

namespace App\Filters\Stores;

use App\Filters\BaseModelFilter;
use App\GraphQL\InputTypes\Stores\Distributors\CoordinateInRadiusFilterInput;
use App\Models\Locations\State;
use App\Models\Locations\Zipcode;
use App\Models\Stores\Distributor;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\ValueObjects\Point;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Distributor
 */
class DistributorFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;

    public const TABLE = Distributor::TABLE;

    public function query(string $query): void
    {
        if ($state = $this->getStateByZip($query)) {
            $this->state($state->id);
        } else {
            $querySearch = makeSearchSlug($query);

            $this->where(
                static fn(Builder $builder) => $builder
                    ->orWhereRaw('`address_metaphone` LIKE ?', ["%$querySearch%"])
            );
        }
    }

    protected function getStateByZip(string $zip): ?State
    {
        return State::query()
            ->whereHas(
                'zipcodes',
                static fn(Builder|Zipcode $b) => $b->where('zip', $zip)
            )
            ->first();
    }

    public function state(int $state): void
    {
        $this->where('state_id', $state);
    }

    /**
     * @param array $coordinates
     * @return void
     * @see CoordinateInRadiusFilterInput
     */
    public function radius(array $coordinates): void
    {
        $this->addDistance(Point::byCoordinates($coordinates), $distance = 'distance_in_km')
            ->having(
                $distance,
                '<=',
                $coordinates['radius']
            );
    }
}
