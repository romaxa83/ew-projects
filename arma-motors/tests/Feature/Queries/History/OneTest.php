<?php

namespace Tests\Feature\Queries\History;

use App\DTO\History\HistoryCarDto;
use App\Exceptions\ErrorsCode;
use App\Models\History\CarItem;
use App\Services\History\CarHistoryService;
use Faker\Generator as Faker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\HistoryTestData;
use Tests\Traits\UserBuilder;

class OneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CarBuilder;
    use UserBuilder;
    use HistoryTestData;

    const QUERY = 'history';

    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();

        $this->faker = resolve(Faker::class);
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $data = $this->data($this->faker);
        $data['id'] = $carUuid;

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carUuid)->first();

        $this->assertNotNull($model);

        $this->graphQL($this->getQueryStr($model->id))
            ->assertJson([
                "data" => [
                    self::QUERY => [
                        "id" => $model->id,
                        "carUuid" => $model->car_uuid,
                        "car" => [
                            "id" => $car->id,
                            "uuid" => $carUuid,
                        ],
                        "invoices" => [
                            [
                                "id" => $model->invoices->first()->id,
                                "uuid" => $model->invoices->first()->aa_uuid,
                                "address" => $model->invoices->first()->address,
                                "amountIncludingVAT" => $model->invoices->first()->amount_including_vat,
                                "amountVAT" => $model->invoices->first()->amount_vat,
                                "amountWithoutVAT" => $model->invoices->first()->amount_without_vat,
                                "author" => $model->invoices->first()->author,
                                "contactInformation" => $model->invoices->first()->contact_information,
                                "date" => $model->invoices->first()->date,
                                "discount" => $model->invoices->first()->discount,
                                "etc" => $model->invoices->first()->etc,
                                "number" => $model->invoices->first()->number,
                                "organization" => $model->invoices->first()->organization,
                                "phone" => $model->invoices->first()->phone,
                                "shopper" => $model->invoices->first()->shopper,
                                "taxCode" => $model->invoices->first()->tax_code,
                                "parts" => [
                                    [
                                        "id" => $model->invoices->first()->parts[0]->id,
                                        "name" => $model->invoices->first()->parts[0]->name,
                                        "ref" => $model->invoices->first()->parts[0]->ref,
                                        "unit" => $model->invoices->first()->parts[0]->unit,
                                        "discountedPrice" => $model->invoices->first()->parts[0]->discounted_price,
                                        "price" => $model->invoices->first()->parts[0]->price,
                                        "quantity" => $model->invoices->first()->parts[0]->quantity,
                                        "rate" => $model->invoices->first()->parts[0]->rate,
                                        "sum" => $model->invoices->first()->parts[0]->sum,
                                    ],
                                    [
                                        "id" => $model->invoices->first()->parts[1]->id,
                                        "name" => $model->invoices->first()->parts[1]->name,
                                        "ref" => $model->invoices->first()->parts[1]->ref,
                                        "unit" => $model->invoices->first()->parts[1]->unit,
                                        "discountedPrice" => $model->invoices->first()->parts[1]->discounted_price,
                                        "price" => $model->invoices->first()->parts[1]->price,
                                        "quantity" => $model->invoices->first()->parts[1]->quantity,
                                        "rate" => $model->invoices->first()->parts[1]->rate,
                                        "sum" => $model->invoices->first()->parts[1]->sum,
                                    ],
                                    [
                                        "id" => $model->invoices->first()->parts[2]->id,
                                        "name" => $model->invoices->first()->parts[2]->name,
                                        "ref" => $model->invoices->first()->parts[2]->ref,
                                        "unit" => $model->invoices->first()->parts[2]->unit,
                                        "discountedPrice" => $model->invoices->first()->parts[2]->discounted_price,
                                        "price" => $model->invoices->first()->parts[2]->price,
                                        "quantity" => $model->invoices->first()->parts[2]->quantity,
                                        "rate" => $model->invoices->first()->parts[2]->rate,
                                        "sum" => $model->invoices->first()->parts[2]->sum,
                                    ]
                                ]
                            ]
                        ],
                        "orders" => [
                            [
                                "id" => $model->orders->first()->id,
                                "uuid" => $model->orders->first()->aa_id,
                                "amountInWords" => $model->orders->first()->amount_in_words,
                                "amountIncludingVAT" => $model->orders->first()->amount_including_vat,
                                "amountVAT" => $model->orders->first()->amount_vat,
                                "amountWithoutVAT" => $model->orders->first()->amount_without_vat,
                                "bodyNumber" => $model->orders->first()->body_number,
                                "closingDate" => $model->orders->first()->closing_date,
                                "currentAccount" => $model->orders->first()->current_account,
                                "date" => $model->orders->first()->date,
                                "dateOfSale" => $model->orders->first()->date_of_sale,
                                "dealer" => $model->orders->first()->dealer,
                                "disassembledParts" => $model->orders->first()->disassembled_parts,
                                "discount" => $model->orders->first()->discount,
                                "discountJobs" => $model->orders->first()->discount_jobs,
                                "discountParts" => $model->orders->first()->discount_parts,
                                "jobsAmountIncludingVAT" => $model->orders->first()->jobs_amount_including_vat,
                                "jobsAmountVAT" => $model->orders->first()->jobs_amount_vat,
                                "jobsAmountWithoutVAT" => $model->orders->first()->jobs_amount_without_vat,
                                "model" => $model->orders->first()->model,
                                "number" => $model->orders->first()->number,
                                "partsAmountIncludingVAT" => $model->orders->first()->parts_amount_including_vat,
                                "partsAmountVAT" => $model->orders->first()->parts_amount_vat,
                                "partsAmountWithoutVAT" => $model->orders->first()->parts_amount_without_vat,
                                "producer" => $model->orders->first()->producer,
                                "recommendations" => $model->orders->first()->recommendations,
                                "repairType" => $model->orders->first()->repair_type,
                                "stateNumber" => $model->orders->first()->state_number,
                                "mileage" => $model->orders->first()->mileage,
                                "parts" => [
                                    [
                                        "id" => $model->orders->first()->parts[0]->id,
                                        "name" => $model->orders->first()->parts[0]->name,
                                        "amountIncludingVAT" => $model->orders->first()->parts[0]->amount_including_vat,
                                        "amountWithoutVAT" => $model->orders->first()->parts[0]->amount_without_vat,
                                        "price" => $model->orders->first()->parts[0]->price,
                                        "priceWithVAT" => $model->orders->first()->parts[0]->price_with_vat,
                                        "priceWithoutVAT" => $model->orders->first()->parts[0]->price_without_vat,
                                        "producer" => $model->orders->first()->parts[0]->producer,
                                        "quantity" => $model->orders->first()->parts[0]->quantity,
                                        "unit" => $model->orders->first()->parts[0]->unit,
                                        "ref" => $model->orders->first()->parts[0]->ref,
                                        "rate" => $model->orders->first()->parts[0]->rate,
                                    ],
                                    [
                                        "id" => $model->orders->first()->parts[1]->id,
                                        "name" => $model->orders->first()->parts[1]->name,
                                        "amountIncludingVAT" => $model->orders->first()->parts[1]->amount_including_vat,
                                        "amountWithoutVAT" => $model->orders->first()->parts[1]->amount_without_vat,
                                        "price" => $model->orders->first()->parts[1]->price,
                                        "priceWithVAT" => $model->orders->first()->parts[1]->price_with_vat,
                                        "priceWithoutVAT" => $model->orders->first()->parts[1]->price_without_vat,
                                        "producer" => $model->orders->first()->parts[1]->producer,
                                        "quantity" => $model->orders->first()->parts[1]->quantity,
                                        "unit" => $model->orders->first()->parts[1]->unit,
                                        "ref" => $model->orders->first()->parts[1]->ref,
                                        "rate" => $model->orders->first()->parts[1]->rate,
                                    ],
                                ],
                                "jobs" => [
                                    [
                                        "id" => $model->orders->first()->jobs[0]->id,
                                        "name" => $model->orders->first()->jobs[0]->name,
                                        "amountIncludingVAT" => $model->orders->first()->jobs[0]->amount_including_vat,
                                        "amountWithoutVAT" => $model->orders->first()->jobs[0]->amount_without_vat,
                                        "coefficient" => $model->orders->first()->jobs[0]->coefficient,
                                        "price" => $model->orders->first()->jobs[0]->price,
                                        "priceWithVAT" => $model->orders->first()->jobs[0]->price_with_vat,
                                        "priceWithoutVAT" => $model->orders->first()->jobs[0]->price_without_vat,
                                        "ref" => $model->orders->first()->jobs[0]->ref,
                                        "rate" => $model->orders->first()->jobs[0]->rate,
                                    ],
                                    [
                                        "id" => $model->orders->first()->jobs[1]->id,
                                        "name" => $model->orders->first()->jobs[1]->name,
                                        "amountIncludingVAT" => $model->orders->first()->jobs[1]->amount_including_vat,
                                        "amountWithoutVAT" => $model->orders->first()->jobs[1]->amount_without_vat,
                                        "coefficient" => $model->orders->first()->jobs[1]->coefficient,
                                        "price" => $model->orders->first()->jobs[1]->price,
                                        "priceWithVAT" => $model->orders->first()->jobs[1]->price_with_vat,
                                        "priceWithoutVAT" => $model->orders->first()->jobs[1]->price_without_vat,
                                        "ref" => $model->orders->first()->jobs[1]->ref,
                                        "rate" => $model->orders->first()->jobs[1]->rate,
                                    ],
                                    [
                                        "id" => $model->orders->first()->jobs[2]->id,
                                        "name" => $model->orders->first()->jobs[2]->name,
                                        "amountIncludingVAT" => $model->orders->first()->jobs[2]->amount_including_vat,
                                        "amountWithoutVAT" => $model->orders->first()->jobs[2]->amount_without_vat,
                                        "coefficient" => $model->orders->first()->jobs[2]->coefficient,
                                        "price" => $model->orders->first()->jobs[2]->price,
                                        "priceWithVAT" => $model->orders->first()->jobs[2]->price_with_vat,
                                        "priceWithoutVAT" => $model->orders->first()->jobs[2]->price_without_vat,
                                        "ref" => $model->orders->first()->jobs[2]->ref,
                                        "rate" => $model->orders->first()->jobs[2]->rate,
                                    ],
                                ],
                                "customer" => [
                                    "id" => $model->orders->first()->customer->id,
                                    "FIO" => $model->orders->first()->customer->fio,
                                    "date" => $model->orders->first()->customer->date,
                                    "email" => $model->orders->first()->customer->email,
                                    "name" => $model->orders->first()->customer->name,
                                    "number" => $model->orders->first()->customer->number,
                                    "phone" => $model->orders->first()->customer->phone,
                                ],
                                "dispatcher" => [
                                    "id" => $model->orders->first()->dispatcher->id,
                                    "FIO" => $model->orders->first()->dispatcher->fio,
                                    "date" => $model->orders->first()->dispatcher->date,
                                    "name" => $model->orders->first()->dispatcher->name,
                                    "number" => $model->orders->first()->dispatcher->number,
                                    "position" => $model->orders->first()->dispatcher->position,
                                ],
                                "organization" => [
                                    "id" => $model->orders->first()->organization->id,
                                    "address" => $model->orders->first()->organization->address,
                                    "name" => $model->orders->first()->organization->name,
                                    "phone" => $model->orders->first()->organization->phone,
                                ],
                                "owner" => [
                                    "id" => $model->orders->first()->owner->id,
                                    "address" => $model->orders->first()->owner->address,
                                    "name" => $model->orders->first()->owner->name,
                                    "phone" => $model->orders->first()->owner->phone,
                                    "email" => $model->orders->first()->owner->email,
                                    "certificate" => $model->orders->first()->owner->certificate,
                                    "etc" => $model->orders->first()->owner->etc,
                                ],
                                "payer" => [
                                    "id" => $model->orders->first()->payer->id,
                                    "name" => $model->orders->first()->payer->name,
                                    "date" => $model->orders->first()->payer->date,
                                    "number" => $model->orders->first()->payer->number,
                                    "contract" => $model->orders->first()->payer->contract,
                                ]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function not_model()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $this->graphQL($this->getQueryStr(9999))
            ->assertJson([
                "data" => [
                    self::QUERY => null
                ]
            ])
        ;
    }


    public function not_auth()
    {
        $this->graphQL($this->getQueryStr(9999))
            ->assertJson([
                "errors" => [
                    0 => [
                        "message" => __('auth.not auth'),
                        "extensions" => [
                            "code" => ErrorsCode::NOT_AUTH
                        ]
                    ]
                ]
            ])
        ;
    }

    public function getQueryStr(string $id): string
    {
        return  sprintf('{
            %s (id: %s) {
                id,
                carUuid
                car {
                    id
                    uuid
                }
                invoices {
                    id,
                    uuid,
                    address,
                    amountIncludingVAT,
                    amountVAT,
                    amountWithoutVAT,
                    author,
                    contactInformation,
                    date,
                    discount,
                    etc,
                    number,
                    organization,
                    phone,
                    shopper,
                    taxCode,
                    parts {
                        id,
                        name,
                        ref,
                        unit,
                        discountedPrice,
                        price
                        quantity
                        rate
                        sum
                    }
                }
                orders {
                    id
                    uuid
                    amountInWords
                    amountIncludingVAT
                    amountVAT
                    amountWithoutVAT
                    bodyNumber
                    closingDate
                    currentAccount
                    date
                    dateOfSale
                    dealer
                    disassembledParts
                    discount
                    discountJobs
                    discountParts
                    jobsAmountIncludingVAT
                    jobsAmountVAT
                    jobsAmountWithoutVAT
                    model
                    number
                    partsAmountIncludingVAT
                    partsAmountVAT
                    partsAmountWithoutVAT
                    producer
                    recommendations
                    repairType
                    stateNumber
                    mileage
                    parts {
                        id
                        name
                        amountIncludingVAT
                        amountWithoutVAT
                        price
                        priceWithVAT
                        priceWithoutVAT
                        producer
                        quantity
                        unit
                        ref
                        rate
                    }
                    jobs {
                        id
                        name
                        amountIncludingVAT
                        amountWithoutVAT
                        coefficient
                        price
                        priceWithVAT
                        priceWithoutVAT
                        ref
                        rate
                    }
                    customer {
                        id
                        FIO
                        date
                        email
                        name
                        number
                        phone
                    }
                    dispatcher {
                        id
                        FIO
                        date
                        name
                        number
                        position
                    }
                    organization {
                        id
                        address
                        name
                        phone
                    }
                    owner {
                        id
                        address
                        email
                        phone
                        name
                        certificate
                        etc
                    }
                    payer {
                        id
                        name
                        date
                        number
                        contract
                    }
                }
               }
            }',
            self::QUERY,
            $id
        );
    }
}
