<?php

namespace WezomCms\Orders\ModelFilters;

use Auth;
use EloquentFilter\ModelFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use WezomCms\Core\Contracts\Filter\FilterFieldInterface;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Providers\Repositories\ProviderRepository;
use WezomCms\Users\Models\User;

/**
 * Class OrderTrashedFilter
 * @package WezomCms\Orders\ModelFilters
 * @mixin Order
 */
class OrderTrashedFilter extends ModelFilter implements FilterListFieldsInterface
{
    protected array $users = [];

    /**
     * Generate array with filter fields.
     * @return iterable|FilterFieldInterface[]
     */
    public function getFields(): iterable
    {
        /** @var Administrator $admin */
        $admin = Auth::user();

        $status = FilterField::make()
            ->name('status')
            ->label(__('cms-orders::admin.orders.Status'))
            ->size(2)
            ->type(FilterField::TYPE_SELECT)
            ->options(OrderStatus::getForSelect());

        $users = FilterField::make()
            ->type(FilterField::TYPE_SELECT)
            ->options($this->users)
            ->name('user_id')
            ->label(__('cms-users::admin.User'))
            ->class('js-ajax-select2')
            ->attributes(['data-url' => route('admin.users.search')]);

        $providerRepo = resolve(ProviderRepository::class);
        $providers = $providerRepo->getProvidersWithOrdersForSelect();

        $result = [
            FilterField::id(),
            $users,
            FilterField::makeName()->label(__('cms-orders::admin.orders.Name'))->size(2),
            FilterField::make()->name('phone')->label(__('cms-orders::admin.orders.Phone'))->size(2),
            FilterField::make()->name('email')->label(__('cms-orders::admin.orders.E-mail')),
            $status,
            FilterField::published([
                'name' => 'payed',
                'label' => __('cms-orders::admin.orders.Payed'),
                'options' => [
                    1 => __('cms-core::admin.layout.Yes'),
                    0 => __('cms-core::admin.layout.No'),
                ]
            ]),
            FilterField::make()->name('created_at')->label(__('cms-orders::admin.orders.Date'))
                ->type(FilterField::TYPE_DATE_RANGE),
        ];

        if (!$admin->onlyProvider()) {
            $result[] = FilterField::make()
                ->type(FilterField::TYPE_SELECT)
                ->options($providers)
                ->name('provider_id')
                ->label(__('cms-providers::admin.provider.Provider'))
                ->class('js-select2');
        }

        return $result;
    }

    public function restoreSelectedOptions(Request $request): void
    {
        if ($userId = $request->get('user_id')) {
            $user = User::find($userId);
            if ($user) {
                $this->users = [$user->id => $user->full_name];
            }
        }
    }

    public function id($id): void
    {
        $this->where('id', $id);
    }

    public function name($name): void
    {
        $this->related('client', function ($query) use ($name) {
            $query->whereLike('name', $name)
                ->orWhereLike('patronymic', $name)
                ->orWhereLike('surname', $name);
        });
    }

    public function phone($phone): void
    {
        $this->related('client', function ($query) use ($phone) {
            $query->whereRaw(
                'REPLACE(REPLACE(REPLACE(REPLACE(phone, "+", ""), "(", ""), ")", ""), " ", "") LIKE ?',
                '%' . preg_replace('/[^\d]/', '', $phone) . '%'
            );
        });
    }

    public function user($userId): void
    {
        $this->where('user_id', $userId);
    }

    public function email($email): void
    {
        $this->related('client', function ($query) use ($email) {
            $query->whereLike('email', $email);
        });
    }

    public function status($status): void
    {
        $this->where('status_id', $status);
    }

    public function payed($payed): void
    {
        $this->where('payed', $payed);
    }

    public function createdAtFrom($date): void
    {
        $this->where('created_at', '>=', Carbon::parse($date));
    }

    public function createdAtTo($date): void
    {
        $this->where('created_at', '<=', Carbon::parse($date)->endOfDay());
    }

    public function provider($providerId): void
    {
        $this->where('provider_id', $providerId);
    }
}
