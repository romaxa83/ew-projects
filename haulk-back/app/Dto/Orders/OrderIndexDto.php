<?php

namespace App\Dto\Orders;

use App\Dto\BaseFilterDto;
use App\Models\Orders\Order;
use Illuminate\Support\Carbon;

class OrderIndexDto extends BaseFilterDto
{
    private bool $isEmptyStates = true;

    public static function create(array $args): OrderIndexDto
    {
        $dto = new self();

        $dto->args = $args;

        /**@see OrderDocumentFilter::s() */
        $dto->setFilterField('s')
            /**@see OrderDocumentFilter::make() */
            ->setFilterField('make')
            /**@see OrderDocumentFilter::model() */
            ->setFilterField('model')
            /**@see OrderDocumentFilter::year() */
            ->setFilterField('year')
            /**@see OrderDocumentFilter::driverId() */
            ->setFilterField('driver_id')
            /**@see OrderDocumentFilter::dashboardFilter() */
            ->setFilterField('dashboard_filter')
            /**@see OrderDocumentFilter::dispatcherId() */
            ->setFilterField('dispatcher_id')
            /**@see OrderDocumentFilter::attributes() */
            ->setFilterField('attributes')
            /**@see OrderDocumentFilter::invoiceId() */
            ->setFilterField('invoice_id')
            /**@see OrderDocumentFilter::checkId() */
            ->setFilterField('check_id')
            /**@see OrderDocumentFilter::companyName() */
            ->setFilterField('company_name')
            /**@see OrderDocumentFilter::tagId() */
            ->setFilterField('tag_id')
            /**@see OrderDocumentFilter::hasBrokerFee() */
            ->setBooleanFilter('has_broker_fee')
            /**@see OrderDocumentFilter::hasReview() */
            ->setBooleanFilter('has_review')
            /**@see OrderDocumentFilter::state() */
            ->setStateFilter()
            ->setDateFilter()
            ->setPagination()
            ->setOrderBy('')
            ->setOrderType();

        return $dto;
    }

    /**
     * @return OrderIndexDto
     */
    private function setStateFilter(): OrderIndexDto
    {
        /**@see OrderDocumentFilter::withoutDeleted() */
        $this->filter['without_deleted'] = true;
        $states = data_get($this->args, 'state');

        if (empty($states)) {
            return $this;
        }

        $this->isEmptyStates = false;

        /**@see OrderDocumentFilter::state() */
        $this->filter['state'] = $states;
        /**@see OrderDocumentFilter::withoutDeleted() */
        $this->filter['without_deleted'] = !in_array(Order::CALCULATED_STATUS_DELETED, $states, true);

        return $this;
    }

    /**
     * @return OrderIndexDto
     */
    private function setDateFilter(): OrderIndexDto
    {
        $dateType = data_get($this->args, 'date_type');

        if (empty($dateType)) {
            return $this;
        }

        if (empty($this->args['date_from'])) {
            $this->args['date_from'] = '1970-01-01';
        }

        if (empty($this->args['date_to'])) {
            $this->args['date_to'] = Carbon::now()->addDays(100)->toDateString();
        }

        $interval = [
            'from' => Carbon::createFromTimestamp(
                Carbon::createFromTimestamp(strtotime($this->args['date_from']))
                    ->startOfDay()
                    ->getTimestamp()
            )
                ->setTimezone('UTC'),
            'to' => Carbon::createFromTimestamp(
                Carbon::createFromTimestamp(strtotime($this->args['date_to']))
                    ->endOfDay()
                    ->getTimestamp()
            )
                ->setTimezone('UTC')
        ];

        switch ($dateType) {
            /**@see OrderDocumentFilter::invoiceSendDate() */
            case Order::INVOICE_SENT:
                $this->filter['invoice_send_date'] = $interval;
                break;
            /**@see OrderDocumentFilter::paidAtDate() */
            case 'paid_at':
                $this->filter['paid_at_date'] = $interval;
                break;
            /**@see OrderDocumentFilter::createdAtDate() */
            case Order::CREATED_AT:
                $this->filter[Order::CREATED_AT . '_date'] = $interval;
                break;
            /**@see OrderDocumentFilter::destinationDate() */
            default:
                $this->filter['destination_date'] = [
                    'location' => $dateType,
                    'dates' => $interval
                ];
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchFilter(): ?string
    {
        return data_get($this->filter, 's');
    }

    /**
     * @return bool
     */
    public function isNotEmptyStateFilter(): bool
    {
        return !$this->isEmptyStates;
    }
}
