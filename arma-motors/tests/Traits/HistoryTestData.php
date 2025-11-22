<?php

namespace Tests\Traits;

trait HistoryTestData
{
    public function data(
        \Faker\Generator $faker
    ): array
    {
        return $this->dataCustom($faker,
            [
                $this->invoice($faker, [
                    $this->invoicePart($faker),
                    $this->invoicePart($faker),
                    $this->invoicePart($faker),
                ])
            ],
            [
                $this->order($faker, [
                    $this->orderPart($faker),
                    $this->orderPart($faker),
                ], [
                    $this->orderJob($faker),
                    $this->orderJob($faker),
                    $this->orderJob($faker),
                ],
                    $this->orderCustomer($faker),
                    $this->orderDispatcher($faker),
                    $this->orderOrganization($faker),
                    $this->orderOwner($faker),
                    $this->orderPayer($faker),
                )
            ]
        );
    }

    public function dataCustom(
        \Faker\Generator $faker,
        $invoices = [],
        $orders = [],
    ): array
    {
        return [
            "id" => $faker->uuid,
            "invoices" => $invoices,
            "orders" => $orders,
        ];
    }

    public function invoice (\Faker\Generator $faker, $parts = []): array
    {
        return [
            "address" => $faker->streetAddress,
            "amountIncludingVAT" => $faker->randomFloat(2, 1 ,30000),
            "amountVAT" => $faker->randomFloat(2, 1 ,30000),
            "amountWithoutVAT" => $faker->randomFloat(2, 1 ,30000),
            "author" => $faker->name,
            "contactInformation" => $faker->streetAddress,
            "date" => $faker->dateTimeThisYear()->format('d.m.Y'),
            "discount" => $faker->randomFloat(2, 1 ,30000),
            "etc" => $faker->sentence,
            "id" => "Пикассо_" . $faker->uuid,
            "number" => $faker->creditCardNumber,
            "organization" => $faker->sentence,
            "phone" => $faker->phoneNumber,
            "shopper" => $faker->name,
            "taxCode" => $faker->creditCardNumber,
            "parts" => $parts
        ];
    }

    public function invoicePart (\Faker\Generator $faker): array
    {
        return [
            "name" => $faker->sentence,
            "ref" => $faker->uuid,
            "unit" => $faker->word,
            "discountedPrice" => $faker->randomFloat(2, 1 ,30000),
            "price" => $faker->randomFloat(2, 1 ,30000),
            "quantity" => $faker->randomFloat(2, 1 ,30000),
            "rate" => $faker->randomFloat(2, 1 ,30000),
            "sum" => $faker->randomFloat(2, 1 ,30000)
        ];
    }

    public function order (
        \Faker\Generator $faker,
        $parts = [],
        $jobs = [],
        $customer = [],
        $dispatcher = [],
        $organization = [],
        $owner = [],
        $payer = [],
    ): array
    {
        return [
            "id" => "Пикассо_" . $faker->uuid,
            "AmountInWords" => $faker->sentence,
            "AmountIncludingVAT" => $faker->randomFloat(2, 1 ,30000),
            "AmountWithoutVAT" => $faker->randomFloat(2, 1 ,30000),
            "AmountVAT" => $faker->randomFloat(2, 1 ,30000),
            "bodyNumber" => $faker->creditCardNumber,
            "closingDate" => $faker->dateTimeThisYear()->format('d.m.Y'),
            "currentAccount" => $faker->sentence,
            "date" => "10 липня 2018 р.",
            "dateOfSale" => $faker->dateTimeThisYear()->format('d.m.Y'),
            "dealer" => $faker->sentence,
            "disassembledParts" => "10.07.2018",
            "discount" => $faker->randomFloat(2, 1 ,30000),
            "discountJobs" => $faker->randomFloat(2, 1 ,30000),
            "discountParts" => $faker->randomFloat(2, 1 ,30000),
            "jobsAmountIncludingVAT" => $faker->randomFloat(2, 1 ,30000),
            "jobsAmountVAT" => $faker->randomFloat(2, 1 ,30000),
            "jobsAmountWithoutVAT" => $faker->randomFloat(2, 1 ,30000),
            "model" => "{$faker->city}",
            "number" => $faker->creditCardNumber,
            "partsAmountIncludingVAT" => $faker->randomFloat(2, 1 ,30000),
            "partsAmountVAT" => $faker->randomFloat(2, 1 ,30000),
            "partsAmountWithoutVAT" => $faker->randomFloat(2, 1 ,30000),
            "producer" => $faker->city,
            "recommendations" => $faker->sentence,
            "repairType" => $faker->sentence,
            "stateNumber" => $faker->creditCardNumber,
            "mileage" => $faker->randomFloat(2, 1 ,30000),
            "parts" => $parts,
            "jobs" => $jobs,
            "customer" => $customer,
            "dispatcher" => $dispatcher,
            "organization" => $organization,
            "owner" => $owner,
            "payer" => $payer,
        ];
    }

    public function orderPart (\Faker\Generator $faker): array
    {
        return [
            "name" => $faker->sentence,
            "amountIncludingVAT" => $faker->randomFloat(2, 1 ,30000),
            "amountWithoutVAT" => $faker->randomFloat(2, 1 ,30000),
            "price" => $faker->randomFloat(2, 1 ,30000),
            "priceWithVAT" => $faker->randomFloat(2, 1 ,30000),
            "priceWithoutVAT" => $faker->randomFloat(2, 1 ,30000),
            "producer" => $faker->city,
            "quantity" => $faker->randomFloat(2, 1 ,30000),
            "unit" => $faker->word,
            "ref" => $faker->creditCardNumber,
            "rate" => $faker->randomFloat(2, 1 ,30000)
        ];
    }

    public function orderJob (\Faker\Generator $faker): array
    {
        return [
            "name" => $faker->sentence,
            "amountIncludingVAT" => $faker->randomFloat(2, 1 ,30000),
            "amountWithoutVAT" => $faker->randomFloat(2, 1 ,30000),
            "price" => $faker->randomFloat(2, 1 ,30000),
            "priceWithVAT" => $faker->randomFloat(2, 1 ,30000),
            "priceWithoutVAT" => $faker->randomFloat(2, 1 ,30000),
            "ref" => $faker->creditCardNumber,
            "rate" => $faker->randomFloat(2, 1 ,30000),
            "coefficient" => $faker->randomFloat(2, 1 ,30000)
        ];
    }

    public function orderCustomer (\Faker\Generator $faker): array
    {
        return [
            "FIO" => $faker->name,
            "date" => $faker->dateTimeThisYear()->format('d.m.Y'),
            "email" => $faker->email,
            "name" => $faker->name,
            "number" => $faker->creditCardNumber,
            "phone" => $faker->phoneNumber
        ];
    }

    public function orderDispatcher (\Faker\Generator $faker): array
    {
        return [
            "FIO" => $faker->name,
            "date" => $faker->dateTimeThisYear()->format('d.m.Y'),
            "name" => $faker->name,
            "number" => $faker->creditCardNumber,
            "position" => $faker->sentence
        ];
    }

    public function orderOrganization (\Faker\Generator $faker): array
    {
        return [
            "address" => $faker->sentence,
            "phone" => $faker->phoneNumber,
            "name" => $faker->name,
        ];
    }

    public function orderOwner (\Faker\Generator $faker): array
    {
        return [
            "address" => $faker->streetAddress,
            "email" => $faker->email,
            "name" => $faker->name,
            "certificate" => $faker->sentence,
            "phone" => $faker->phoneNumber,
            "etc" => $faker->sentence
        ];
    }

    public function orderPayer (\Faker\Generator $faker): array
    {
        return [
            "name" => $faker->name,
            "date" => $faker->dateTimeThisYear()->format('d.m.Y'),
            "number" => $faker->creditCardNumber,
            "contract" => $faker->sentence
        ];
    }
}

