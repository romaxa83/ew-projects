<?php

namespace App\Dto\Reports;

use App\Documents\Filters\CompanyDocumentFilter;
use App\Dto\BaseFilterDto;
use Illuminate\Support\Carbon;

class CompanyReportDto extends BaseFilterDto
{
    public static function create(array $args): CompanyReportDto
    {
        $dto = new self();

        if (!empty($args['order_by']) && $args['order_by'] === 'total_count') {
            $args['order_by'] = 'order_count';
        }

        $dto->args = $args;

        /**@see CompanyDocumentFilter::paymentStatus() */
        $dto->setFilterField('payment_status')
            /**@see CompanyDocumentFilter::companyName() */
            ->setFilterField('company_name')
            /**@see CompanyDocumentFilter::invoice() */
            ->setFilterField('invoice_id', 'invoice')
            /**@see CompanyDocumentFilter::referenceNumber() */
            ->setFilterField('check_id', 'reference_number')
            /**@see CompanyDocumentFilter::paymentMethodId() */
            ->setFilterField('payment_method_id')
            ->setInvoiceDateFilter()
            ->setPagination(10)
            ->setOrderBy('company_name')
            ->setOrderType();

        return $dto;
    }

    /**
     * @return CompanyReportDto
     */
    private function setInvoiceDateFilter(): CompanyReportDto
    {
        $dateFrom = data_get($this->args, 'invoice_from');
        $dateTo = data_get($this->args, 'invoice_to');

        if (empty($dateFrom) || empty($dateTo)) {
            return $this;
        }

        /**@see CompanyDocumentFilter::invoiceSendDate() */
        $this->filter['invoice_send_date'] = [
            'from' => Carbon::createFromTimestamp(
                Carbon::createFromTimestamp(strtotime($dateFrom))
                    ->startOfDay()
                    ->getTimestamp()
            )->setTimezone('UTC'),
            'to' => Carbon::createFromTimestamp(
                Carbon::createFromTimestamp(strtotime($dateTo))
                    ->startOfDay()
                    ->getTimestamp()
            )->setTimezone('UTC')
        ];

        return $this;
    }

}
