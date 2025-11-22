<?php

namespace Tests\Feature\Http\Api\V1\User\History;

use App\DTO\History\HistoryCarDto;
use App\Models\History\CarItem;
use App\Models\History\Invoice;
use App\Models\History\InvoicePart;
use App\Models\History\Order;
use App\Models\History\OrderJob;
use App\Models\History\OrderPart;
use App\Services\History\CarHistoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\HistoryTestData;
use Tests\Traits\UserBuilder;
use Faker\Generator as Faker;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use HistoryTestData;

    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->armaAuth();

        $this->faker = resolve(Faker::class);
    }

    public function headers()
    {
        return [
            'Authorization' => 'Basic d2V6b20tYXBpOndlem9tLWFwaQ=='
        ];
    }

    /** @test */
    public function success_add_invoice()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['invoices']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();
        $this->assertEmpty($model->invoices);

        $data['invoices'][] = $this->invoice($this->faker, [
            $this->invoicePart($this->faker),
        ]);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertNotEmpty($model->invoices);

        foreach($model->invoices as $k => $invoice){
            /** @var $invoice Invoice */
            $this->assertEquals($invoice->address, data_get($data, "invoices.{$k}.address"));
            $this->assertEquals($invoice->aa_uuid, data_get($data, "invoices.{$k}.id"));
            $this->assertEquals($invoice->amount_including_vat, data_get($data, "invoices.{$k}.amountIncludingVAT"));
            $this->assertEquals($invoice->amount_vat, data_get($data, "invoices.{$k}.amountVAT"));
            $this->assertEquals($invoice->amount_without_vat, data_get($data, "invoices.{$k}.amountWithoutVAT"));
            $this->assertEquals($invoice->author, data_get($data, "invoices.{$k}.author"));
            $this->assertEquals($invoice->contact_information, data_get($data, "invoices.{$k}.contactInformation"));
            $this->assertEquals($invoice->discount, data_get($data, "invoices.{$k}.discount"));
            $this->assertEquals($invoice->etc, data_get($data, "invoices.{$k}.etc"));
            $this->assertEquals($invoice->number, data_get($data, "invoices.{$k}.number"));
            $this->assertEquals($invoice->organization, data_get($data, "invoices.{$k}.organization"));
            $this->assertEquals($invoice->phone, data_get($data, "invoices.{$k}.phone"));
            $this->assertEquals($invoice->shopper, data_get($data, "invoices.{$k}.shopper"));
            $this->assertEquals($invoice->tax_code, data_get($data, "invoices.{$k}.taxCode"));
            $this->assertEquals(
                $invoice->date->format('Y-m-d'),
                Carbon::createFromFormat('d.m.Y',data_get($data, "invoices.{$k}.date"))->format('Y-m-d')
            );
        }
    }

    /** @test */
    public function success_update_invoice()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['invoices'][0]['parts']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $data['invoices'][0] = [
            "id" => $model->invoices->first()->aa_uuid,
            "address" => $this->faker->streetAddress,
            "amountIncludingVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "amountVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "amountWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "author" => $this->faker->name,
            "contactInformation" => $this->faker->streetAddress,
            "date" => null,
            "discount" => $this->faker->randomFloat(2, 1 ,30000),
            "etc" => $this->faker->sentence,
            "number" => $this->faker->creditCardNumber,
            "organization" => $this->faker->sentence,
            "phone" => $this->faker->phoneNumber,
            "shopper" => $this->faker->name,
            "taxCode" => $this->faker->creditCardNumber,
        ];

        $this->assertNotEquals($model->invoices->first()->address, data_get($data, "invoices.0.address"));
        $this->assertNotEquals($model->invoices->first()->amount_including_vat, data_get($data, "invoices.0.amountIncludingVAT"));
        $this->assertNotEquals($model->invoices->first()->amount_vat, data_get($data, "invoices.0.amountVAT"));
        $this->assertNotEquals($model->invoices->first()->amount_without_vat, data_get($data, "invoices.0.amountWithoutVAT"));
        $this->assertNotEquals($model->invoices->first()->author, data_get($data, "invoices.0.author"));
        $this->assertNotEquals($model->invoices->first()->contact_information, data_get($data, "invoices.0.contactInformation"));
        $this->assertNotEquals($model->invoices->first()->discount, data_get($data, "invoices.0.discount"));
        $this->assertNotEquals($model->invoices->first()->etc, data_get($data, "invoices.0.etc"));
        $this->assertNotEquals($model->invoices->first()->number, data_get($data, "invoices.0.number"));
        $this->assertNotEquals($model->invoices->first()->organization, data_get($data, "invoices.0.organization"));
        $this->assertNotEquals($model->invoices->first()->phone, data_get($data, "invoices.0.phone"));
        $this->assertNotEquals($model->invoices->first()->shopper, data_get($data, "invoices.0.shopper"));
        $this->assertNotEquals($model->invoices->first()->tax_code, data_get($data, "invoices.0.taxCode"));
        $this->assertNotNull($model->invoices->first()->date);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        foreach($model->invoices as $k => $invoice){
            /** @var $invoice Invoice */
            $this->assertEquals($invoice->address, data_get($data, "invoices.{$k}.address"));
            $this->assertEquals($invoice->aa_uuid, data_get($data, "invoices.{$k}.id"));
            $this->assertEquals($invoice->amount_including_vat, data_get($data, "invoices.{$k}.amountIncludingVAT"));
            $this->assertEquals($invoice->amount_vat, data_get($data, "invoices.{$k}.amountVAT"));
            $this->assertEquals($invoice->amount_without_vat, data_get($data, "invoices.{$k}.amountWithoutVAT"));
            $this->assertEquals($invoice->author, data_get($data, "invoices.{$k}.author"));
            $this->assertEquals($invoice->contact_information, data_get($data, "invoices.{$k}.contactInformation"));
            $this->assertEquals($invoice->discount, data_get($data, "invoices.{$k}.discount"));
            $this->assertEquals($invoice->etc, data_get($data, "invoices.{$k}.etc"));
            $this->assertEquals($invoice->number, data_get($data, "invoices.{$k}.number"));
            $this->assertEquals($invoice->organization, data_get($data, "invoices.{$k}.organization"));
            $this->assertEquals($invoice->phone, data_get($data, "invoices.{$k}.phone"));
            $this->assertEquals($invoice->shopper, data_get($data, "invoices.{$k}.shopper"));
            $this->assertEquals($invoice->tax_code, data_get($data, "invoices.{$k}.taxCode"));
            $this->assertNull($invoice->date);
        }
    }

    /** @test */
    public function success_add_invoice_part()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['invoices'][0]['parts']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertEmpty($model->invoices->first()->parts);

        $data['invoices'][0]['parts'][0] = $this->invoicePart($this->faker);
        $data['invoices'][0]['parts'][1] = $this->invoicePart($this->faker);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertNotEmpty($model->invoices->first()->parts);

        foreach($model->invoices as $k => $invoice){
            /** @var $invoice Invoice */
            foreach ($invoice->parts as $key => $part){
                /** @var $part InvoicePart */
                $this->assertEquals($part->name, data_get($data, "invoices.{$k}.parts.{$key}.name"));
                $this->assertEquals($part->ref, data_get($data, "invoices.{$k}.parts.{$key}.ref"));
                $this->assertEquals($part->unit, data_get($data, "invoices.{$k}.parts.{$key}.unit"));
                $this->assertEquals($part->discounted_price, data_get($data, "invoices.{$k}.parts.{$key}.discountedPrice"));
                $this->assertEquals($part->price, data_get($data, "invoices.{$k}.parts.{$key}.price"));
                $this->assertEquals($part->quantity, data_get($data, "invoices.{$k}.parts.{$key}.quantity"));
                $this->assertEquals($part->rate, data_get($data, "invoices.{$k}.parts.{$key}.rate"));
                $this->assertEquals($part->sum, data_get($data, "invoices.{$k}.parts.{$key}.sum"));
            }
        }
    }

    /** @test */
    public function success_update_invoice_part()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $data['invoices'][0]['parts'] = [
            [
                "name" => $this->faker->sentence,
                "ref" => $this->faker->uuid,
                "unit" => $this->faker->word,
                "discountedPrice" => $this->faker->randomFloat(2, 1 ,30000),
                "price" => $this->faker->randomFloat(2, 1 ,30000),
                "quantity" => $this->faker->randomFloat(2, 1 ,30000),
                "rate" => $this->faker->randomFloat(2, 1 ,30000),
                "sum" => $this->faker->randomFloat(2, 1 ,30000)
            ],
            [
                "name" => $this->faker->sentence,
                "ref" => $this->faker->uuid,
                "unit" => $this->faker->word,
                "discountedPrice" => $this->faker->randomFloat(2, 1 ,30000),
                "price" => $this->faker->randomFloat(2, 1 ,30000),
                "quantity" => $this->faker->randomFloat(2, 1 ,30000),
                "rate" => $this->faker->randomFloat(2, 1 ,30000),
                "sum" => $this->faker->randomFloat(2, 1 ,30000)
            ],
            [
                "name" => $this->faker->sentence,
                "ref" => $this->faker->uuid,
                "unit" => $this->faker->word,
                "discountedPrice" => $this->faker->randomFloat(2, 1 ,30000),
                "price" => $this->faker->randomFloat(2, 1 ,30000),
                "quantity" => $this->faker->randomFloat(2, 1 ,30000),
                "rate" => $this->faker->randomFloat(2, 1 ,30000),
                "sum" => $this->faker->randomFloat(2, 1 ,30000)
            ]
        ];

        foreach($model->invoices as $k => $invoice){
            /** @var $invoice Invoice */
            foreach ($invoice->parts as $key => $part){
                /** @var $part InvoicePart */
                $this->assertNotEquals($part->name, data_get($data, "invoices.{$k}.parts.{$key}.name"));
                $this->assertNotEquals($part->ref, data_get($data, "invoices.{$k}.parts.{$key}.ref"));
                $this->assertNotEquals($part->unit, data_get($data, "invoices.{$k}.parts.{$key}.unit"));
                $this->assertNotEquals($part->discounted_price, data_get($data, "invoices.{$k}.parts.{$key}.discountedPrice"));
                $this->assertNotEquals($part->price, data_get($data, "invoices.{$k}.parts.{$key}.price"));
                $this->assertNotEquals($part->quantity, data_get($data, "invoices.{$k}.parts.{$key}.quantity"));
                $this->assertNotEquals($part->rate, data_get($data, "invoices.{$k}.parts.{$key}.rate"));
                $this->assertNotEquals($part->sum, data_get($data, "invoices.{$k}.parts.{$key}.sum"));
            }
        }

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        foreach($model->invoices as $k => $invoice){
            /** @var $invoice Invoice */
            foreach ($invoice->parts as $key => $part){
                /** @var $part InvoicePart */
                $this->assertEquals($part->name, data_get($data, "invoices.{$k}.parts.{$key}.name"));
                $this->assertEquals($part->ref, data_get($data, "invoices.{$k}.parts.{$key}.ref"));
                $this->assertEquals($part->unit, data_get($data, "invoices.{$k}.parts.{$key}.unit"));
                $this->assertEquals($part->discounted_price, data_get($data, "invoices.{$k}.parts.{$key}.discountedPrice"));
                $this->assertEquals($part->price, data_get($data, "invoices.{$k}.parts.{$key}.price"));
                $this->assertEquals($part->quantity, data_get($data, "invoices.{$k}.parts.{$key}.quantity"));
                $this->assertEquals($part->rate, data_get($data, "invoices.{$k}.parts.{$key}.rate"));
                $this->assertEquals($part->sum, data_get($data, "invoices.{$k}.parts.{$key}.sum"));
            }
        }
    }

    /** @test */
    public function success_add_order()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();
        $this->assertEmpty($model->orders);

        $data['orders'][] = $this->order($this->faker);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertNotEmpty($model->orders);

        foreach($model->orders as $k => $order){
            /** @var $order Order */
            $this->assertEquals($order->aa_id, data_get($data, "orders.{$k}.id"));
            $this->assertEquals($order->amount_in_words, data_get($data, "orders.{$k}.AmountInWords"));
            $this->assertEquals($order->amount_including_vat, data_get($data, "orders.{$k}.AmountIncludingVAT"));
            $this->assertEquals($order->amount_without_vat, data_get($data, "orders.{$k}.AmountWithoutVAT"));
            $this->assertEquals($order->amount_vat, data_get($data, "orders.{$k}.AmountVAT"));
            $this->assertEquals($order->body_number, data_get($data, "orders.{$k}.bodyNumber"));
            $this->assertEquals($order->current_account, data_get($data, "orders.{$k}.currentAccount"));
            $this->assertEquals($order->date, data_get($data, "orders.{$k}.date"));
            $this->assertEquals($order->dealer, data_get($data, "orders.{$k}.dealer"));
            $this->assertEquals($order->disassembled_parts, data_get($data, "orders.{$k}.disassembledParts"));
            $this->assertEquals($order->discount, data_get($data, "orders.{$k}.discount"));
            $this->assertEquals($order->discount_jobs, data_get($data, "orders.{$k}.discountJobs"));
            $this->assertEquals($order->discount_parts, data_get($data, "orders.{$k}.discountParts"));
            $this->assertEquals($order->jobs_amount_including_vat, data_get($data, "orders.{$k}.jobsAmountIncludingVAT"));
            $this->assertEquals($order->jobs_amount_vat, data_get($data, "orders.{$k}.jobsAmountVAT"));
            $this->assertEquals($order->jobs_amount_without_vat, data_get($data, "orders.{$k}.jobsAmountWithoutVAT"));
            $this->assertEquals($order->model, data_get($data, "orders.{$k}.model"));
            $this->assertEquals($order->number, data_get($data, "orders.{$k}.number"));
            $this->assertEquals($order->parts_amount_including_vat, data_get($data, "orders.{$k}.partsAmountIncludingVAT"));
            $this->assertEquals($order->parts_amount_vat, data_get($data, "orders.{$k}.partsAmountVAT"));
            $this->assertEquals($order->parts_amount_without_vat, data_get($data, "orders.{$k}.partsAmountWithoutVAT"));
            $this->assertEquals($order->producer, data_get($data, "orders.{$k}.producer"));
            $this->assertEquals($order->recommendations, data_get($data, "orders.{$k}.recommendations"));
            $this->assertEquals($order->repair_type, data_get($data, "orders.{$k}.repairType"));
            $this->assertEquals($order->state_number, data_get($data, "orders.{$k}.stateNumber"));
            $this->assertEquals($order->mileage, data_get($data, "orders.{$k}.mileage"));
        }
    }

    /** @test */
    public function success_update_order()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders'][0]['parts']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $data['orders'][0] = [
            "id" => $model->orders->first()->aa_id,
            "AmountInWords" => $this->faker->sentence,
            "AmountIncludingVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "AmountWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "AmountVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "bodyNumber" => $this->faker->creditCardNumber,
            "closingDate" => $this->faker->dateTimeThisYear()->format('d.m.Y'),
            "currentAccount" => $this->faker->sentence,
            "date" => "10 липня 2018 р.",
            "dateOfSale" => $this->faker->dateTimeThisYear()->format('d.m.Y'),
            "dealer" => $this->faker->sentence,
            "disassembledParts" => "10.07.2018",
            "discount" => $this->faker->randomFloat(2, 1 ,30000),
            "discountJobs" => $this->faker->randomFloat(2, 1 ,30000),
            "discountParts" => $this->faker->randomFloat(2, 1 ,30000),
            "jobsAmountIncludingVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "jobsAmountVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "jobsAmountWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "model" => "{$this->faker->unique->city}",
            "number" => $this->faker->creditCardNumber,
            "partsAmountIncludingVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "partsAmountVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "partsAmountWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
            "producer" => $this->faker->city,
            "recommendations" => $this->faker->sentence,
            "repairType" => $this->faker->sentence,
            "stateNumber" => $this->faker->creditCardNumber,
            "mileage" => $this->faker->randomFloat(2, 1 ,30000),
        ];

        $this->assertNotEquals($model->orders->first()->amount_in_words, data_get($data, "orders.0.AmountInWords"));
        $this->assertNotEquals($model->orders->first()->amount_including_vat, data_get($data, "orders.0.AmountIncludingVAT"));
        $this->assertNotEquals($model->orders->first()->amount_without_vat, data_get($data, "orders.0.AmountWithoutVAT"));
        $this->assertNotEquals($model->orders->first()->amount_vat, data_get($data, "orders.0.AmountVAT"));
        $this->assertNotEquals($model->orders->first()->body_number, data_get($data, "orders.0.bodyNumber"));
        $this->assertNotEquals($model->orders->first()->current_account, data_get($data, "orders.0.currentAccount"));
        $this->assertNotEquals($model->orders->first()->dealer, data_get($data, "orders.0.dealer"));
        $this->assertNotEquals($model->orders->first()->discount, data_get($data, "orders.0.discount"));
        $this->assertNotEquals($model->orders->first()->discount_jobs, data_get($data, "orders.0.discountJobs"));
        $this->assertNotEquals($model->orders->first()->discount_parts, data_get($data, "orders.0.discountParts"));
        $this->assertNotEquals($model->orders->first()->jobs_amount_including_vat, data_get($data, "orders.0.jobsAmountIncludingVAT"));
        $this->assertNotEquals($model->orders->first()->jobs_amount_vat, data_get($data, "orders.0.jobsAmountVAT"));
        $this->assertNotEquals($model->orders->first()->jobs_amount_without_vat, data_get($data, "orders.0.jobsAmountWithoutVAT"));
        $this->assertNotEquals($model->orders->first()->model, data_get($data, "orders.0.model"));
        $this->assertNotEquals($model->orders->first()->number, data_get($data, "orders.0.number"));
        $this->assertNotEquals($model->orders->first()->parts_amount_including_vat, data_get($data, "orders.0.partsAmountIncludingVAT"));
        $this->assertNotEquals($model->orders->first()->parts_amount_vat, data_get($data, "orders.0.partsAmountVAT"));
        $this->assertNotEquals($model->orders->first()->parts_amount_without_vat, data_get($data, "orders.0.partsAmountWithoutVAT"));
        $this->assertNotEquals($model->orders->first()->producer, data_get($data, "orders.0.producer"));
        $this->assertNotEquals($model->orders->first()->recommendations, data_get($data, "orders.0.recommendations"));
        $this->assertNotEquals($model->orders->first()->repair_type, data_get($data, "orders.0.repairType"));
        $this->assertNotEquals($model->orders->first()->state_number, data_get($data, "orders.0.stateNumber"));
        $this->assertNotEquals($model->orders->first()->mileage, data_get($data, "orders.0.mileage"));

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        foreach($model->orders as $k => $order){
            /** @var $order Order */
            $this->assertEquals($order->aa_id, data_get($data, "orders.{$k}.id"));
            $this->assertEquals($order->amount_in_words, data_get($data, "orders.{$k}.AmountInWords"));
            $this->assertEquals($order->amount_including_vat, data_get($data, "orders.{$k}.AmountIncludingVAT"));
            $this->assertEquals($order->amount_without_vat, data_get($data, "orders.{$k}.AmountWithoutVAT"));
            $this->assertEquals($order->amount_vat, data_get($data, "orders.{$k}.AmountVAT"));
            $this->assertEquals($order->body_number, data_get($data, "orders.{$k}.bodyNumber"));
            $this->assertEquals($order->current_account, data_get($data, "orders.{$k}.currentAccount"));
            $this->assertEquals($order->date, data_get($data, "orders.{$k}.date"));
            $this->assertEquals($order->dealer, data_get($data, "orders.{$k}.dealer"));
            $this->assertEquals($order->disassembled_parts, data_get($data, "orders.{$k}.disassembledParts"));
            $this->assertEquals($order->discount, data_get($data, "orders.{$k}.discount"));
            $this->assertEquals($order->discount_jobs, data_get($data, "orders.{$k}.discountJobs"));
            $this->assertEquals($order->discount_parts, data_get($data, "orders.{$k}.discountParts"));
            $this->assertEquals($order->jobs_amount_including_vat, data_get($data, "orders.{$k}.jobsAmountIncludingVAT"));
            $this->assertEquals($order->jobs_amount_vat, data_get($data, "orders.{$k}.jobsAmountVAT"));
            $this->assertEquals($order->jobs_amount_without_vat, data_get($data, "orders.{$k}.jobsAmountWithoutVAT"));
            $this->assertEquals($order->model, data_get($data, "orders.{$k}.model"));
            $this->assertEquals($order->number, data_get($data, "orders.{$k}.number"));
            $this->assertEquals($order->parts_amount_including_vat, data_get($data, "orders.{$k}.partsAmountIncludingVAT"));
            $this->assertEquals($order->parts_amount_vat, data_get($data, "orders.{$k}.partsAmountVAT"));
            $this->assertEquals($order->parts_amount_without_vat, data_get($data, "orders.{$k}.partsAmountWithoutVAT"));
            $this->assertEquals($order->producer, data_get($data, "orders.{$k}.producer"));
            $this->assertEquals($order->recommendations, data_get($data, "orders.{$k}.recommendations"));
            $this->assertEquals($order->repair_type, data_get($data, "orders.{$k}.repairType"));
            $this->assertEquals($order->state_number, data_get($data, "orders.{$k}.stateNumber"));
            $this->assertEquals($order->mileage, data_get($data, "orders.{$k}.mileage"));
        }
    }

    /** @test */
    public function success_add_order_part()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders'][0]['parts']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertEmpty($model->orders->first()->parts);

        $data['orders'][0]['parts'][0] = $this->orderPart($this->faker);
        $data['orders'][0]['parts'][1] = $this->orderPart($this->faker);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertNotEmpty($model->orders->first()->parts);

        foreach($model->orders as $k => $order){
            /** @var $order Order */
            foreach ($order->parts as $key => $part){
                /** @var $part OrderPart */
                $this->assertEquals($part->name, data_get($data, "orders.{$k}.parts.{$key}.name"));
                $this->assertEquals($part->amount_including_vat, data_get($data, "orders.{$k}.parts.{$key}.amountIncludingVAT"));
                $this->assertEquals($part->amount_without_vat, data_get($data, "orders.{$k}.parts.{$key}.amountWithoutVAT"));
                $this->assertEquals($part->price, data_get($data, "orders.{$k}.parts.{$key}.price"));
                $this->assertEquals($part->price_with_vat, data_get($data, "orders.{$k}.parts.{$key}.priceWithVAT"));
                $this->assertEquals($part->price_without_vat, data_get($data, "orders.{$k}.parts.{$key}.priceWithoutVAT"));
                $this->assertEquals($part->producer, data_get($data, "orders.{$k}.parts.{$key}.producer"));
                $this->assertEquals($part->quantity, data_get($data, "orders.{$k}.parts.{$key}.quantity"));
                $this->assertEquals($part->unit, data_get($data, "orders.{$k}.parts.{$key}.unit"));
                $this->assertEquals($part->ref, data_get($data, "orders.{$k}.parts.{$key}.ref"));
                $this->assertEquals($part->rate, data_get($data, "orders.{$k}.parts.{$key}.rate"));
            }
        }
    }

    /** @test */
    public function success_update_order_part()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $data['orders'][0]['parts'] = [
            [
                "name" => $this->faker->sentence,
                "amountIncludingVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "amountWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "price" => $this->faker->randomFloat(2, 1 ,30000),
                "priceWithVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "priceWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "producer" => $this->faker->randomElement,
                "quantity" => $this->faker->randomFloat(2, 1 ,30000),
                "unit" => $this->faker->word,
                "ref" => $this->faker->creditCardNumber,
                "rate" => $this->faker->randomFloat(2, 1 ,30000)
            ],
            [
                "name" => $this->faker->sentence,
                "amountIncludingVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "amountWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "price" => $this->faker->randomFloat(2, 1 ,30000),
                "priceWithVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "priceWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "producer" => $this->faker->randomElement,
                "quantity" => $this->faker->randomFloat(2, 1 ,30000),
                "unit" => $this->faker->unique->word,
                "ref" => $this->faker->creditCardNumber,
                "rate" => $this->faker->randomFloat(2, 1 ,30000)
            ],
        ];

        foreach($model->orders as $k => $order){
            /** @var $order Order */
            foreach ($order->parts as $key => $part){
                /** @var $part OrderPart */
                $this->assertNotEquals($part->name, data_get($data, "orders.{$k}.parts.{$key}.name"));
                $this->assertNotEquals($part->amount_including_vat, data_get($data, "orders.{$k}.parts.{$key}.amountIncludingVAT"));
                $this->assertNotEquals($part->amount_without_vat, data_get($data, "orders.{$k}.parts.{$key}.amountWithoutVAT"));
                $this->assertNotEquals($part->price, data_get($data, "orders.{$k}.parts.{$key}.price"));
                $this->assertNotEquals($part->price_with_vat, data_get($data, "orders.{$k}.parts.{$key}.priceWithVAT"));
                $this->assertNotEquals($part->price_without_vat, data_get($data, "orders.{$k}.parts.{$key}.priceWithoutVAT"));
                $this->assertNotEquals($part->producer, data_get($data, "orders.{$k}.parts.{$key}.producer"));
                $this->assertNotEquals($part->quantity, data_get($data, "orders.{$k}.parts.{$key}.quantity"));
                $this->assertNotEquals($part->unit, data_get($data, "orders.{$k}.parts.{$key}.unit"));
                $this->assertNotEquals($part->ref, data_get($data, "orders.{$k}.parts.{$key}.ref"));
                $this->assertNotEquals($part->rate, data_get($data, "orders.{$k}.parts.{$key}.rate"));
            }
        }

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        foreach($model->orders as $k => $order){
            /** @var $order Order */
            foreach ($order->parts as $key => $part){
                /** @var $part OrderPart */
                $this->assertEquals($part->name, data_get($data, "orders.{$k}.parts.{$key}.name"));
                $this->assertEquals($part->amount_including_vat, data_get($data, "orders.{$k}.parts.{$key}.amountIncludingVAT"));
                $this->assertEquals($part->amount_without_vat, data_get($data, "orders.{$k}.parts.{$key}.amountWithoutVAT"));
                $this->assertEquals($part->price, data_get($data, "orders.{$k}.parts.{$key}.price"));
                $this->assertEquals($part->price_with_vat, data_get($data, "orders.{$k}.parts.{$key}.priceWithVAT"));
                $this->assertEquals($part->price_without_vat, data_get($data, "orders.{$k}.parts.{$key}.priceWithoutVAT"));
                $this->assertEquals($part->producer, data_get($data, "orders.{$k}.parts.{$key}.producer"));
                $this->assertEquals($part->quantity, data_get($data, "orders.{$k}.parts.{$key}.quantity"));
                $this->assertEquals($part->unit, data_get($data, "orders.{$k}.parts.{$key}.unit"));
                $this->assertEquals($part->ref, data_get($data, "orders.{$k}.parts.{$key}.ref"));
                $this->assertEquals($part->rate, data_get($data, "orders.{$k}.parts.{$key}.rate"));
            }
        }
    }

    /** @test */
    public function success_add_order_job()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders'][0]['jobs']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertEmpty($model->orders->first()->jobs);

        $data['orders'][0]['jobs'][0] = $this->orderJob($this->faker);
        $data['orders'][0]['jobs'][1] = $this->orderJob($this->faker);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertNotEmpty($model->orders->first()->jobs);

        foreach($model->orders as $k => $order){
            /** @var $order Order */
            foreach ($order->jobs as $key => $job){
                /** @var $job OrderPart */
                $this->assertEquals($job->name, data_get($data, "orders.{$k}.jobs.{$key}.name"));
                $this->assertEquals($job->amount_including_vat, data_get($data, "orders.{$k}.jobs.{$key}.amountIncludingVAT"));
                $this->assertEquals($job->amount_without_vat, data_get($data, "orders.{$k}.jobs.{$key}.amountWithoutVAT"));
                $this->assertEquals($job->coefficient, data_get($data, "orders.{$k}.jobs.{$key}.coefficient"));
                $this->assertEquals($job->price, data_get($data, "orders.{$k}.jobs.{$key}.price"));
                $this->assertEquals($job->price_with_vat, data_get($data, "orders.{$k}.jobs.{$key}.priceWithVAT"));
                $this->assertEquals($job->price_without_vat, data_get($data, "orders.{$k}.jobs.{$key}.priceWithoutVAT"));
                $this->assertEquals($job->ref, data_get($data, "orders.{$k}.jobs.{$key}.ref"));
                $this->assertEquals($job->rate, data_get($data, "orders.{$k}.jobs.{$key}.rate"));
            }
        }
    }

    /** @test */
    public function success_update_order_job()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $data['orders'][0]['jobs'] = [
            [
                "name" => $this->faker->sentence,
                "amountIncludingVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "amountWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "price" => $this->faker->randomFloat(2, 1 ,30000),
                "priceWithVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "priceWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "ref" => $this->faker->creditCardNumber,
                "rate" => $this->faker->randomFloat(2, 1 ,30000),
                "coefficient" => $this->faker->randomFloat(2, 1 ,30000)
            ],
            [
                "name" => $this->faker->sentence,
                "amountIncludingVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "amountWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "price" => $this->faker->randomFloat(2, 1 ,30000),
                "priceWithVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "priceWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "ref" => $this->faker->creditCardNumber,
                "rate" => $this->faker->randomFloat(2, 1 ,30000),
                "coefficient" => $this->faker->randomFloat(2, 1 ,30000)
            ],
            [
                "name" => $this->faker->sentence,
                "amountIncludingVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "amountWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "price" => $this->faker->randomFloat(2, 1 ,30000),
                "priceWithVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "priceWithoutVAT" => $this->faker->randomFloat(2, 1 ,30000),
                "ref" => $this->faker->creditCardNumber,
                "rate" => $this->faker->randomFloat(2, 1 ,30000),
                "coefficient" => $this->faker->randomFloat(2, 1 ,30000)
            ],
        ];

        foreach($model->orders as $k => $order){
            /** @var $order Order */
            foreach ($order->jobs as $key => $job){
                /** @var $job OrderJob */
                $this->assertNotEquals($job->name, data_get($data, "orders.{$k}.jobs.{$key}.name"));
                $this->assertNotEquals($job->amount_including_vat, data_get($data, "orders.{$k}.jobs.{$key}.amountIncludingVAT"));
                $this->assertNotEquals($job->amount_without_vat, data_get($data, "orders.{$k}.jobs.{$key}.amountWithoutVAT"));
                $this->assertNotEquals($job->coefficient, data_get($data, "orders.{$k}.jobs.{$key}.coefficient"));
                $this->assertNotEquals($job->price, data_get($data, "orders.{$k}.jobs.{$key}.price"));
                $this->assertNotEquals($job->price_with_vat, data_get($data, "orders.{$k}.jobs.{$key}.priceWithVAT"));
                $this->assertNotEquals($job->price_without_vat, data_get($data, "orders.{$k}.jobs.{$key}.priceWithoutVAT"));
                $this->assertNotEquals($job->ref, data_get($data, "orders.{$k}.jobs.{$key}.ref"));
                $this->assertNotEquals($job->rate, data_get($data, "orders.{$k}.jobs.{$key}.rate"));
            }
        }

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        foreach($model->orders as $k => $order){
            /** @var $order Order */
            foreach ($order->jobs as $key => $job){
                /** @var $job OrderJob */
                $this->assertEquals($job->name, data_get($data, "orders.{$k}.jobs.{$key}.name"));
                $this->assertEquals($job->amount_including_vat, data_get($data, "orders.{$k}.jobs.{$key}.amountIncludingVAT"));
                $this->assertEquals($job->amount_without_vat, data_get($data, "orders.{$k}.jobs.{$key}.amountWithoutVAT"));
                $this->assertEquals($job->coefficient, data_get($data, "orders.{$k}.jobs.{$key}.coefficient"));
                $this->assertEquals($job->price, data_get($data, "orders.{$k}.jobs.{$key}.price"));
                $this->assertEquals($job->price_with_vat, data_get($data, "orders.{$k}.jobs.{$key}.priceWithVAT"));
                $this->assertEquals($job->price_without_vat, data_get($data, "orders.{$k}.jobs.{$key}.priceWithoutVAT"));
                $this->assertEquals($job->ref, data_get($data, "orders.{$k}.jobs.{$key}.ref"));
                $this->assertEquals($job->rate, data_get($data, "orders.{$k}.jobs.{$key}.rate"));
            }
        }
    }

    /** @test */
    public function success_add_order_customer()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders'][0]['customer']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertEmpty($model->orders->first()->customer);

        $data['orders'][0]['customer'] = $this->orderCustomer($this->faker);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertNotEmpty($model->orders->first()->customer);

        foreach($model->orders as $k => $order){
            $this->assertEquals($order->customer->fio, data_get($data, "orders.{$k}.customer.FIO"));
            $this->assertEquals($order->customer->email, data_get($data, "orders.{$k}.customer.email"));
            $this->assertEquals($order->customer->name, data_get($data, "orders.{$k}.customer.name"));
            $this->assertEquals($order->customer->number, data_get($data, "orders.{$k}.customer.number"));
            $this->assertEquals($order->customer->phone, data_get($data, "orders.{$k}.customer.phone"));
            if(data_get($data, "orders.{$k}.customer.date")){
                $this->assertEquals(
                    $order->customer->date->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.customer.date"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->customer->date);
            }
        }
    }

    /** @test */
    public function success_update_order_customer()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $data['orders'][0]['customer'] = [
            "FIO" => $this->faker->name,
            "date" => $this->faker->dateTimeThisYear()->format('d.m.Y'),
            "email" => $this->faker->email,
            "name" => $this->faker->name,
            "number" => $this->faker->creditCardNumber,
            "phone" => $this->faker->phoneNumber
        ];

        foreach($model->orders as $k => $order){
            $this->assertNotEquals($order->customer->fio, data_get($data, "orders.{$k}.customer.FIO"));
            $this->assertNotEquals($order->customer->email, data_get($data, "orders.{$k}.customer.email"));
            $this->assertNotEquals($order->customer->name, data_get($data, "orders.{$k}.customer.name"));
            $this->assertNotEquals($order->customer->number, data_get($data, "orders.{$k}.customer.number"));
            $this->assertNotEquals($order->customer->phone, data_get($data, "orders.{$k}.customer.phone"));
            if(data_get($data, "orders.{$k}.customer.date")){
                $this->assertNotEquals(
                    $order->customer->date->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.customer.date"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->customer->date);
            }
        }

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        foreach($model->orders as $k => $order){
            $this->assertEquals($order->customer->fio, data_get($data, "orders.{$k}.customer.FIO"));
            $this->assertEquals($order->customer->email, data_get($data, "orders.{$k}.customer.email"));
            $this->assertEquals($order->customer->name, data_get($data, "orders.{$k}.customer.name"));
            $this->assertEquals($order->customer->number, data_get($data, "orders.{$k}.customer.number"));
            $this->assertEquals($order->customer->phone, data_get($data, "orders.{$k}.customer.phone"));
            if(data_get($data, "orders.{$k}.customer.date")){
                $this->assertEquals(
                    $order->customer->date->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.customer.date"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->customer->date);
            }
        }
    }

    /** @test */
    public function success_add_order_dispatcher()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders'][0]['dispatcher']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertNull($model->orders->first()->dispatcher);

        $data['orders'][0]['dispatcher'] = $this->orderDispatcher($this->faker);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->orders->first()->dispatcher);

        foreach($model->orders as $k => $order){
            $this->assertEquals($order->dispatcher->fio, data_get($data, "orders.{$k}.dispatcher.FIO"));
            $this->assertEquals($order->dispatcher->position, data_get($data, "orders.{$k}.dispatcher.position"));
            $this->assertEquals($order->dispatcher->name, data_get($data, "orders.{$k}.dispatcher.name"));
            $this->assertEquals($order->dispatcher->number, data_get($data, "orders.{$k}.dispatcher.number"));
            if(data_get($data, "orders.{$k}.dispatcher.date")){
                $this->assertEquals(
                    $order->dispatcher->date->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.dispatcher.date"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->dispatcher->date);
            }
        }
    }

    /** @test */
    public function success_update_order_dispatcher()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $data['orders'][0]['dispatcher'] = [
            "FIO" => $this->faker->name,
            "date" => $this->faker->dateTimeThisYear()->format('d.m.Y'),
            "name" => $this->faker->name,
            "number" => $this->faker->creditCardNumber,
            "position" => $this->faker->sentence
        ];

        foreach($model->orders as $k => $order){
            $this->assertNotEquals($order->dispatcher->fio, data_get($data, "orders.{$k}.dispatcher.FIO"));
            $this->assertNotEquals($order->dispatcher->position, data_get($data, "orders.{$k}.dispatcher.position"));
            $this->assertNotEquals($order->dispatcher->name, data_get($data, "orders.{$k}.dispatcher.name"));
            $this->assertNotEquals($order->dispatcher->number, data_get($data, "orders.{$k}.dispatcher.number"));
            if(data_get($data, "orders.{$k}.dispatcher.date")){
                $this->assertNotEquals(
                    $order->dispatcher->date->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.dispatcher.date"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->dispatcher->date);
            }
        }

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        foreach($model->orders as $k => $order){
            $this->assertEquals($order->dispatcher->fio, data_get($data, "orders.{$k}.dispatcher.FIO"));
            $this->assertEquals($order->dispatcher->position, data_get($data, "orders.{$k}.dispatcher.position"));
            $this->assertEquals($order->dispatcher->name, data_get($data, "orders.{$k}.dispatcher.name"));
            $this->assertEquals($order->dispatcher->number, data_get($data, "orders.{$k}.dispatcher.number"));
            if(data_get($data, "orders.{$k}.dispatcher.date")){
                $this->assertEquals(
                    $order->dispatcher->date->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.dispatcher.date"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->dispatcher->date);
            }
        }
    }

    /** @test */
    public function success_add_order_organization()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders'][0]['organization']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertNull($model->orders->first()->organization);

        $data['orders'][0]['organization'] = $this->orderOrganization($this->faker);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->orders->first()->organization);

        foreach($model->orders as $k => $order){
            $this->assertEquals($order->organization->address, data_get($data, "orders.{$k}.organization.address"));
            $this->assertEquals($order->organization->phone, data_get($data, "orders.{$k}.organization.phone"));
            $this->assertEquals($order->organization->name, data_get($data, "orders.{$k}.organization.name"));
        }
    }

    /** @test */
    public function success_update_order_organization()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $data['orders'][0]['organization'] = [
            "address" => $this->faker->sentence,
            "phone" => $this->faker->phoneNumber,
            "name" => $this->faker->name,
        ];

        foreach($model->orders as $k => $order){
            $this->assertNotEquals($order->organization->address, data_get($data, "orders.{$k}.organization.address"));
            $this->assertNotEquals($order->organization->phone, data_get($data, "orders.{$k}.organization.phone"));
            $this->assertNotEquals($order->organization->name, data_get($data, "orders.{$k}.organization.name"));
        }

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        foreach($model->orders as $k => $order){
            $this->assertEquals($order->organization->address, data_get($data, "orders.{$k}.organization.address"));
            $this->assertEquals($order->organization->phone, data_get($data, "orders.{$k}.organization.phone"));
            $this->assertEquals($order->organization->name, data_get($data, "orders.{$k}.organization.name"));
        }
    }

    /** @test */
    public function success_add_order_owner()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders'][0]['owner']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertNull($model->orders->first()->owner);

        $data['orders'][0]['owner'] = $this->orderOwner($this->faker);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->orders->first()->owner);

        foreach($model->orders as $k => $order){
            $this->assertEquals($order->owner->address, data_get($data, "orders.{$k}.owner.address"));
            $this->assertEquals($order->owner->email, data_get($data, "orders.{$k}.owner.email"));
            $this->assertEquals($order->owner->name, data_get($data, "orders.{$k}.owner.name"));
            $this->assertEquals($order->owner->certificate, data_get($data, "orders.{$k}.owner.certificate"));
            $this->assertEquals($order->owner->phone, data_get($data, "orders.{$k}.owner.phone"));
            $this->assertEquals($order->owner->etc, data_get($data, "orders.{$k}.owner.etc"));
        }
    }

    /** @test */
    public function success_update_order_owner()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $data['orders'][0]['owner'] = [
            "address" => $this->faker->streetAddress,
            "email" => $this->faker->email,
            "name" => $this->faker->name,
            "certificate" => $this->faker->sentence,
            "phone" => $this->faker->phoneNumber,
            "etc" => $this->faker->sentence
        ];

        foreach($model->orders as $k => $order){
            $this->assertNotEquals($order->owner->address, data_get($data, "orders.{$k}.owner.address"));
            $this->assertNotEquals($order->owner->email, data_get($data, "orders.{$k}.owner.email"));
            $this->assertNotEquals($order->owner->name, data_get($data, "orders.{$k}.owner.name"));
            $this->assertNotEquals($order->owner->certificate, data_get($data, "orders.{$k}.owner.certificate"));
            $this->assertNotEquals($order->owner->phone, data_get($data, "orders.{$k}.owner.phone"));
            $this->assertNotEquals($order->owner->etc, data_get($data, "orders.{$k}.owner.etc"));
        }

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        foreach($model->orders as $k => $order){
            $this->assertEquals($order->owner->address, data_get($data, "orders.{$k}.owner.address"));
            $this->assertEquals($order->owner->email, data_get($data, "orders.{$k}.owner.email"));
            $this->assertEquals($order->owner->name, data_get($data, "orders.{$k}.owner.name"));
            $this->assertEquals($order->owner->certificate, data_get($data, "orders.{$k}.owner.certificate"));
            $this->assertEquals($order->owner->phone, data_get($data, "orders.{$k}.owner.phone"));
            $this->assertEquals($order->owner->etc, data_get($data, "orders.{$k}.owner.etc"));
        }
    }

    /** @test */
    public function success_add_order_payer()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders'][0]['payer']);

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertNull($model->orders->first()->payer);

        $data['orders'][0]['payer'] = $this->orderOwner($this->faker);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->orders->first()->payer);

        foreach($model->orders as $k => $order){
            $this->assertEquals($order->payer->name, data_get($data, "orders.{$k}.payer.name"));
            $this->assertEquals($order->payer->number, data_get($data, "orders.{$k}.payer.number"));
            $this->assertEquals($order->payer->contract, data_get($data, "orders.{$k}.payer.contract"));
            if(data_get($data, "orders.{$k}.payer.date")){
                $this->assertEquals(
                    $order->payer->date->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.payer.date"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->payer->date);
            }
        }
    }

    /** @test */
    public function success_update_order_payer()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        // создаем запись
        app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $data['orders'][0]['payer'] = [
            "name" => $this->faker->name,
            "date" => $this->faker->dateTimeThisYear()->format('d.m.Y'),
            "number" => $this->faker->creditCardNumber,
            "contract" => $this->faker->sentence
        ];

        foreach($model->orders as $k => $order){
            $this->assertNotEquals($order->payer->name, data_get($data, "orders.{$k}.payer.name"));
            $this->assertNotEquals($order->payer->number, data_get($data, "orders.{$k}.payer.number"));
            $this->assertNotEquals($order->payer->contract, data_get($data, "orders.{$k}.payer.contract"));
            if(data_get($data, "orders.{$k}.payer.date")){
                $this->assertNotEquals(
                    $order->payer->date->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.payer.date"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->payer->date);
            }
        }

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model->refresh();

        foreach($model->orders as $k => $order){
            $this->assertEquals($order->payer->name, data_get($data, "orders.{$k}.payer.name"));
            $this->assertEquals($order->payer->number, data_get($data, "orders.{$k}.payer.number"));
            $this->assertEquals($order->payer->contract, data_get($data, "orders.{$k}.payer.contract"));
            if(data_get($data, "orders.{$k}.payer.date")){
                $this->assertEquals(
                    $order->payer->date->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.payer.date"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->payer->date);
            }
        }
    }
}

