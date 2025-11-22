<?php


namespace App\Filters\Chat;


use App\Filters\BaseModelFilter;
use App\Models\Chat\ChatMenu;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use Illuminate\Database\Eloquent\Builder;

class ChatMenuFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use LikeRawFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;

    public function query(string $query): void
    {
        $this
            ->whereHas(
                'menu_translations',
                fn(Builder $builder) => $this->likeRaw('title', $query, $builder)
            );
    }

    public function action(string $action): void
    {
        $this->where('action', $action);
    }

    public function withoutParent(bool $withoutParent): void
    {
        if ($withoutParent) {
            $this->whereNull('parent_id');
            return;
        }

        $this->whereNotNull('parent_id');
    }

    protected function allowedOrders(): array
    {
        return ChatMenu::ALLOWED_SORTING_FIELDS;
    }
}
