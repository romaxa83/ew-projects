<?php

namespace WezomCms\ServicesOrders\ModelFilters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Dealerships\Repositories\DealershipRepository;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\ServicesOrders\Types\OrderStatus;

/**
 * Class ServicesOrderFilter
 * @package WezomCms\ServicesOrders\ModelFilters
 * @mixin ServicesOrder
 */
class ServicesOrderFilter extends ModelFilter implements FilterListFieldsInterface
{
    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        $rateParam = 'services-orders-rates';
        $route = explode('/', url()->current());

        $status = FilterField::make()
            ->name('status')
            ->label(__('cms-services-orders::admin.Status'))
            ->size(2)
            ->type(FilterField::TYPE_SELECT)
            ->options(OrderStatus::forSelect());

        $dealershipRepository = \App::make(DealershipRepository::class);
        $dealership = FilterField::make()
            ->name('dealership')
            ->label(__('cms-dealerships::admin.Dealerships'))
            ->size(2)
            ->type(FilterField::TYPE_SELECT)
            ->options($dealershipRepository->forSelect());

        $dateRange = FilterField::make()
            ->name('rate_date')
            ->label(__('cms-services-orders::admin.Date rate'))
            ->type(FilterField::TYPE_DATE_RANGE);

        $fields = [
            FilterField::id(),
            $dealership
        ];

        if(last($route) !== $rateParam){
            array_push($fields, $status);
        } else {
            array_push($fields, $dateRange);
        }

        return $fields;
    }

    public function id($id)
    {
        $this->where('id', $id);
    }

    public function status($status)
    {
        $this->where('status', $status);
    }

    public function dealership($dealershipId)
    {
        $this->where('dealership_id', $dealershipId);
    }

//    public function rateDateFrom($date)
//    {
//        $this->where('rate_date', '>=', Carbon::parse($date));
//    }
//
//    public function rateDateTo($date)
//    {
//        $this->where('rate_date', '<=', Carbon::parse($date)->endOfDay());
//    }
}
