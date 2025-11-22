<?php

namespace App\Filters\Clients;

use App\Models\Clients\Client;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class ClientFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;
    use LikeRawFilterTrait;

    public function query(string $query): void
    {
        $this->where(
            fn(Builder $builder) => $builder->orWhere(
                fn(Builder $like) => $this->likeRaw('name', $query, $like)
            )
                ->orWhere(
                    fn(Builder $like) => $this->likeRaw('contact_person', $query, $like)
                )
                ->orWhere(
                    fn(Builder $like) => $this->likeRaw('edrpou', $query, $like)
                )
                ->orWhereHas(
                    'phones',
                    fn(Builder $like) => $this->likeRaw('phone', $query, $like)
                )
                ->orWhereHas(
                    'manager',
                    fn(Builder $manager) => $manager->whereHas(
                        'phones',
                        fn(Builder $like) => $this->likeRaw('phone', $query, $like)
                    )
                )
                ->orWhereHas(
                    'manager',
                    fn(Builder $manager) => $this->likeRaw(
                        "CONCAT_WS(' ', `last_name`,`first_name`, `second_name`)",
                        $query,
                        $manager
                    )
                )
        );
    }

    protected function allowedOrders(): array
    {
        return Client::ALLOWED_SORTING_FIELDS;
    }
}
