<?php

namespace App\DTO\Order;

use App\Models\Order\Order;
use Illuminate\Support\Arr;

class OrderPdfDTO
{
    private array $temp = [];
    private array $data = [];

    public function __construct()
    {}

    public function fill(array $data, string $type): array
    {
        $this->data = $data;

        if($type == Order::FILE_BILL_TYPE){
            $this->byBill();
        }
        if($type == Order::FILE_ACT_TYPE){
            $this->byAct();
        }

        return $this->temp;
    }

    private function byBill(): void
    {
        $this->temp = [
            "title" => $this->data['title'] ?? null,
            "contactInformation" => $this->data['contactInformation'] ?? null,
            "date" => $this->data['date'] ?? null,
            "organization" => $this->data['organization'] ?? null,
            "number" => $this->data['number'] ?? null,
            "shopper" => $this->data['shopper'] ?? null,
            "address" => $this->data['address'] ?? null,
            "phone" => $this->data['phone'] ?? null,
            "etc" => $this->data['etc'] ?? null,
            "taxCode" => $this->data['taxCode'] ?? null,
            "discount" => $this->data['discount'] ?? null,
            "amountWithoutVAT" => $this->data['amountWithoutVAT'] ?? null,
            "amountIncludingVAT" => $this->data['amountIncludingVAT'] ?? null,
            "amountVAT" => $this->data['amountVAT'] ?? null,
            "author" => $this->data['author'] ?? null,
            "parts" => $this->data['parts'] ?? [],
        ];
    }

    private function byAct(): void
    {
        $this->temp = [
            "title" => Arr::get($this->data, 'title'),
            "jobsAmountVAT" => Arr::get($this->data, 'jobsAmountVAT'),
            "payer" => [
                "name" => Arr::get($this->data, 'payer.name'),
                "date" => Arr::get($this->data, 'payer.date'),
                "contract" => Arr::get($this->data, 'payer.contract'),
                "number" => Arr::get($this->data, 'payer.number'),
            ],
            "repairType" => Arr::get($this->data, 'repairType'),
            "number" => Arr::get($this->data, 'number'),
            "closingDate" => Arr::get($this->data, 'closingDate'),
            "organization" => [
                "name" => Arr::get($this->data, 'organization.name'),
                "phone" => Arr::get($this->data, 'organization.phone'),
                "address" => Arr::get($this->data, 'organization.address'),
            ],
            "dealer" => Arr::get($this->data, 'dealer'),
            "jobs" => Arr::get($this->data, 'jobs'),
            "AmountInWords" => Arr::get($this->data, 'AmountInWords'),
            "date" => Arr::get($this->data, 'date'),
            "mileage" => Arr::get($this->data, 'mileage'),
            "currentAccount" => Arr::get($this->data, 'currentAccount'),
            "owner" => [
                "name" => Arr::get($this->data, 'owner.name'),
                "phone" => Arr::get($this->data, 'owner.phone'),
                "address" => Arr::get($this->data, 'owner.address'),
                "email" => Arr::get($this->data, 'owner.email'),
                "etc" => Arr::get($this->data, 'owner.etc'),
                "certificate" => Arr::get($this->data, 'owner.certificate'),
            ],
            "partsAmountIncludingVAT" => Arr::get($this->data, 'partsAmountIncludingVAT'),
            "customer" => [
                "name" => Arr::get($this->data, 'customer.name'),
                "phone" => Arr::get($this->data, 'customer.phone'),
                "FIO" => Arr::get($this->data, 'customer.FIO'),
                "email" => Arr::get($this->data, 'customer.email'),
                "date" => Arr::get($this->data, 'customer.date'),
                "number" => Arr::get($this->data, 'customer.number'),
            ],
            "model" => Arr::get($this->data, 'model'),
            "bodyNumber" => Arr::get($this->data, 'bodyNumber'),
            "dateOfSale" => Arr::get($this->data, 'dateOfSale'),
            "stateNumber" => Arr::get($this->data, 'stateNumber'),
            "producer" => Arr::get($this->data, 'producer'),
            "dispatcher" => [
                "position" => Arr::get($this->data, 'dispatcher.position'),
                "name" => Arr::get($this->data, 'dispatcher.name'),
                "FIO" => Arr::get($this->data, 'dispatcher.FIO'),
                "date" => Arr::get($this->data, 'dispatcher.date'),
                "number" => Arr::get($this->data, 'dispatcher.number'),
            ],
            "parts" => Arr::get($this->data, 'parts'),
            "disassembledParts" => Arr::get($this->data, 'disassembledParts'),
            "AmountIncludingVAT" => Arr::get($this->data, 'AmountIncludingVAT'),
            "recommendations" => Arr::get($this->data, 'recommendations'),
            "AmountVAT" => Arr::get($this->data, 'AmountVAT'),
            "discountParts" => Arr::get($this->data, 'discountParts'),
            "discountJobs" => Arr::get($this->data, 'discountJobs'),
            "discount" => Arr::get($this->data, 'discount'),
            "jobsAmountWithoutVAT" => Arr::get($this->data, 'jobsAmountWithoutVAT'),
            "jobsAmountIncludingVAT" => Arr::get($this->data, 'jobsAmountIncludingVAT'),
            "partsAmountWithoutVAT" => Arr::get($this->data, 'partsAmountWithoutVAT'),
            "partsAmountVAT" => Arr::get($this->data, 'partsAmountVAT'),
            "AmountWithoutVAT" => Arr::get($this->data, 'AmountWithoutVAT'),
        ];
    }
}
