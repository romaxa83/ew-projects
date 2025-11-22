<?php


namespace App\Filters\Vehicles;


use App\Filters\BaseModelFilter;
use App\Models\Vehicles\Vehicle;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use App\Traits\Model\ModeratedScopeTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class VehicleFilter
 * @package App\Filters\Vehicles
 *
 * @see ModeratedScopeTrait::scopeFilterModerated()
 * @method filterModerated(bool $isModerated)
 */
class VehicleFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use SortFilterTrait;
    use LikeRawFilterTrait;

    public function vin(string $vin): void
    {
        $this->likeRaw('vin', $vin);
    }

    public function stateNumber(string $stateNumber): void
    {
        $this->likeRaw('state_number', $stateNumber);
    }

    public function form(string $form): void
    {
        $this->where('form', $form);
    }

    public function class(int $id): void
    {
        $this->where('class_id', $id);
    }

    public function type(int $id): void
    {
        $this->where('type_id', $id);
    }

    public function make(int $id): void
    {
        $this->where('make_id', $id);
    }

    public function model(int $id): void
    {
        $this->where('model_id', $id);
    }

    public function schema(int $id): void
    {
        $this->where('schema_id', $id);
    }

    public function client(int $id): void
    {
        $this->where('client_id', $id);
    }

    public function manager(int $id): void
    {
        $this->whereHas(
            'client',
            fn(Builder $builder) => $builder->where('manager_id', $id)
        );
    }

    public function isModerated(bool $isModerated): void
    {
        $this->filterModerated($isModerated);
    }

    public function dateInspectionFrom(string $date): void
    {
        $date = Carbon::createFromFormat('Y-m-d', $date)
            ->startOfDay()
            ->toDateTimeString();

        $this->whereHas(
            'inspections',
            fn(Builder $builder) => $builder->where('created_at', '>=', $date)
        );
    }

    public function dateInspectionTo(string $date): void
    {
        $date = Carbon::createFromFormat('Y-m-d', $date)
            ->endOfDay()
            ->toDateTimeString();

        $this->whereHas(
            'inspections',
            fn(Builder $builder) => $builder->where('created_at', '<=', $date)
        );
    }

    protected function allowedOrders(): array
    {
        return Vehicle::ALLOWED_SORTING_FIELDS;
    }
}
