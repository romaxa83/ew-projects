<?php


namespace App\Filters\Inspections;


use App\Filters\BaseModelFilter;
use App\Models\Inspections\Inspection;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class InspectionFilter
 * @package App\Filters\Inspections
 *
 * @mixin Inspection
 */
class InspectionFilter extends BaseModelFilter
{
    use SortFilterTrait;
    use LikeRawFilterTrait;
    use IdFilterTrait;

    public function stateNumber(string $statNumber): void
    {
        $this->whereHas(
            'vehicle',
            fn(Builder $builder) => $this->likeRaw('state_number', $statNumber, $builder)
        );
    }

    public function vehicleForm(string $vehicleFrom): void
    {
        $this->whereHas(
            'vehicle',
            fn(Builder $builder) => $builder->where('form', $vehicleFrom)
        );
    }

    public function withoutConnection(bool $flag): void
    {
        if ($flag) {
            $this
                ->whereDoesntHave('trailer')
                ->whereDoesntHave('main');
            return;
        }

       $this->where(
           fn(Builder $builder) =>
                $builder->whereHas('trailer')
                    ->orWhereHas('main')
       );
    }

    public function moderated(bool $moderated): void
    {
        $this->filterModerated($moderated);
    }

    public function dateFrom(string $date): void
    {
        $this->where(
            'created_at',
            '>=',
            Carbon::parse($date)
                ->startOfDay()
        );
    }

    public function dateTo(string $date): void
    {
        $this->where(
            'created_at',
            '<=',
            Carbon::parse($date)
                ->endOfDay()
        );
    }

    protected function allowedOrders(): array
    {
        return Inspection::ALLOWED_ORDERED_FIELDS;
    }

    public function inspector(int $inspectorId): void
    {
        $this->where('inspector_id', $inspectorId);
    }
}
