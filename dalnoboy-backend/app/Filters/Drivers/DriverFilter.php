<?php


namespace App\Filters\Drivers;


use App\Filters\BaseModelFilter;
use App\Models\Drivers\Driver;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use Illuminate\Database\Eloquent\Builder;

class DriverFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use LikeRawFilterTrait;
    use SortFilterTrait;

    public function query(string $query): void
    {
        $this->where(
            fn(Builder $builder) => $builder->orWhere(
                fn(Builder $like) => $this->likeRaw($this->getFullNameField(), $query, $like)
            )
                ->orWhere(
                    fn(Builder $like) => $this->likeRaw('email', $query, $like)
                )
                ->orWhereHas(
                    'phones',
                    fn(Builder $like) => $this->likeRaw('phone', $query, $like)
                )
                ->orWhereHas(
                    'client',
                    fn(Builder $client) => $client
                        ->whereHas(
                            'phones',
                            fn(Builder $like) => $this->likeRaw('phone', $query, $like)
                        )
                        ->orWhere(
                            fn(Builder $like) => $this->likeRaw(
                                'name',
                                $query,
                                $like
                            )
                        )
                )
        );
    }

    private function getFullNameField(): string
    {
        $userTable = $this
            ->getModel()
            ->getTable();

        $fields = [
            $userTable . '.`last_name`',
            $userTable . '.`first_name`',
            $userTable . '.`second_name`',
        ];

        return "CONCAT_WS(' '," . implode(',', $fields) . ")";
    }

    public function customFullNameSort(string $direction): void
    {
        $this->orderByRaw($this->getFullNameField() . " " . $direction);
    }

    protected function allowedOrders(): array
    {
        return Driver::ALLOWED_SORTING_FIELDS;
    }
}
