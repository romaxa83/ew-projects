<?php

namespace App\DTO\History;

use App\DTO\Order\OrderPdfDTO;
use App\Models\Order\Order;

final class OrderDto
{
    public null|string $aa_id;
    public null|string $sys_id;
    public null|string $amount_in_words;
    public null|float $amount_including_vat;
    public null|float $amount_without_vat;
    public null|float $amount_vat;
    public null|string $body_number;
    public null|string $closing_date;
    public null|string $current_account;
    public null|string $date;
    public null|string $date_of_sale;
    public null|string $dealer;
    public null|string $disassembled_parts;
    public null|float $discount;
    public null|float $discount_jobs;
    public null|float $discount_parts;
    public null|float $jobs_amount_including_vat;
    public null|float $jobs_amount_vat;
    public null|float $jobs_amount_without_vat;
    public null|string $model;
    public null|string $number;
    public null|float $parts_amount_including_vat;
    public null|float $parts_amount_vat;
    public null|float $parts_amount_without_vat;
    public null|string $producer;
    public null|string $recommendations;
    public null|string $repair_type;
    public null|string $state_number;
    public null|float $mileage;

    public array $parts = [];
    public array $jobs = [];

    public null|OrderCustomerDto $customer = null;
    public null|OrderDispatcherDto $dispatcher = null;
    public null|OrderOrganizationDto $organization = null;
    public null|OrderOwnerDto $owner = null;
    public null|OrderPayerDto $payer = null;

    public $pdfData;

    private function __construct()
    {}

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->aa_id = $data['id'] ?? null;
        $self->sys_id = $data['sys_id'] ?? null;
        $self->amount_in_words = $data['AmountInWords'] ?? null;
        $self->amount_including_vat = $data['AmountIncludingVAT'] ?? null;
        $self->amount_without_vat = $data['AmountWithoutVAT'] ?? null;
        $self->amount_vat = $data['AmountVAT'] ?? null;
        $self->body_number = $data['bodyNumber'] ?? null;
        $self->closing_date = $data['closingDate'] ?? null;
        $self->current_account = $data['currentAccount'] ?? null;
        $self->date = $data['date'] ?? null;
        $self->date_of_sale = $data['dateOfSale'] ?? null;
        $self->dealer = $data['dealer'] ?? null;
        $self->disassembled_parts = $data['disassembledParts'] ?? null;
        $self->discount = $data['discount'] ?? null;
        $self->discount_jobs = $data['discountJobs'] ?? null;
        $self->discount_parts = $data['discountParts'] ?? null;
        $self->jobs_amount_including_vat = $data['jobsAmountIncludingVAT'] ?? null;
        $self->jobs_amount_vat = $data['jobsAmountVAT'] ?? null;
        $self->jobs_amount_without_vat = $data['jobsAmountWithoutVAT'] ?? null;
        $self->model = $data['model'] ?? null;
        $self->number = $data['number'] ?? null;
        $self->parts_amount_including_vat = $data['partsAmountIncludingVAT'] ?? null;
        $self->parts_amount_vat = $data['partsAmountVAT'] ?? null;
        $self->parts_amount_without_vat = $data['partsAmountWithoutVAT'] ?? null;
        $self->producer = $data['producer'] ?? null;
        $self->recommendations = $data['recommendations'] ?? null;
        $self->repair_type = $data['repairType'] ?? null;
        $self->state_number = $data['stateNumber'] ?? null;
        $self->mileage = $data['mileage'] ?? null;

        foreach ($data['parts'] ?? [] as  $part){
            $self->parts[] = OrderPartDto::byRequest($part);
        }

        foreach ($data['jobs'] ?? [] as  $job){
            $self->jobs[] = OrderJobDto::byRequest($job);
        }

        $self->customer = isset($data['customer'])
            ? OrderCustomerDto::byRequest($data['customer']) : null;

        $self->dispatcher = isset($data['dispatcher'])
            ? OrderDispatcherDto::byRequest($data['dispatcher']) : null;

        $self->organization = isset($data['organization'])
            ? OrderOrganizationDto::byRequest($data['organization']) : null;

        $self->owner = isset($data['owner'])
            ? OrderOwnerDto::byRequest($data['owner']) : null;

        $self->payer = isset($data['payer'])
            ? OrderPayerDto::byRequest($data['payer']) : null;

        $self->pdfData = app(OrderPdfDTO::class)->fill($data, Order::FILE_ACT_TYPE);

        return $self;
    }
}
