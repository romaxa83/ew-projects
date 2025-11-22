<?php

namespace Tests\Feature\Http\Api\V1\User\History;

use App\Models\History\CarItem;
use App\Models\History\Invoice;
use App\Models\History\InvoicePart;
use App\Models\History\Order;
use App\Models\History\OrderJob;
use App\Models\History\OrderPart;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\HistoryTestData;
use Tests\Traits\UserBuilder;
use Faker\Generator as Faker;

class CreateTest extends TestCase
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
    public function success_create()
    {
        $data = $this->data($this->faker);
        $carId = $data['id'];

        Storage::fake();

        $this->assertNull(CarItem::query()->where('car_uuid', $carId)->first());

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model = CarItem::query()->where('car_uuid', $carId)->first();

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

            if(data_get($data, "orders.{$k}.dateOfSale")){
                $this->assertEquals(
                    $order->date_of_sale->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.dateOfSale"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->date_of_sale);
            }
            if(data_get($data, "orders.{$k}.closingDate")){
                $this->assertEquals(
                    $order->closing_date->format('Y-m-d'),
                    Carbon::createFromFormat('d.m.Y',data_get($data, "orders.{$k}.closingDate"))->format('Y-m-d')
                );
            } else {
                $this->assertNull($order->closing_date);
            }

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

            if(data_get($data, "orders.{$k}.customer")){
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

            if(data_get($data, "orders.{$k}.dispatcher")){
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

            if(data_get($data, "orders.{$k}.organization")){
                $this->assertEquals($order->organization->address, data_get($data, "orders.{$k}.organization.address"));
                $this->assertEquals($order->organization->phone, data_get($data, "orders.{$k}.organization.phone"));
                $this->assertEquals($order->organization->name, data_get($data, "orders.{$k}.organization.name"));
            }

            if(data_get($data, "orders.{$k}.owner")){
                $this->assertEquals($order->owner->address, data_get($data, "orders.{$k}.owner.address"));
                $this->assertEquals($order->owner->email, data_get($data, "orders.{$k}.owner.email"));
                $this->assertEquals($order->owner->name, data_get($data, "orders.{$k}.owner.name"));
                $this->assertEquals($order->owner->certificate, data_get($data, "orders.{$k}.owner.certificate"));
                $this->assertEquals($order->owner->phone, data_get($data, "orders.{$k}.owner.phone"));
                $this->assertEquals($order->owner->etc, data_get($data, "orders.{$k}.owner.etc"));
            }

            if(data_get($data, "orders.{$k}.payer")){
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

    /** @test */
    public function success_create_without_invoice()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['invoices']);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertEmpty($model->invoices);
        $this->assertNotEmpty($model->orders);
    }

    /** @test */
    public function success_create_without_invoice_part()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['invoices'][0]['parts']);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertNotEmpty($model->invoices);
        $this->assertEmpty($model->invoices->first()->parts);
        $this->assertNotEmpty($model->orders);
    }

    /** @test */
    public function success_create_without_order()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders']);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertNotEmpty($model->invoices);
        $this->assertEmpty($model->orders);
    }

    /** @test */
    public function success_create_without_order_part()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders'][0]['parts']);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertNotEmpty($model->invoices);
        $this->assertNotEmpty($model->orders);
        $this->assertEmpty($model->orders->first()->parts);
    }

    /** @test */
    public function success_create_without_order_job()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset($data['orders'][0]['jobs']);

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertNotEmpty($model->invoices);
        $this->assertNotEmpty($model->orders);
        $this->assertEmpty($model->orders->first()->jobs);
    }

    /** @test */
    public function success_create_without_order_other_relate()
    {
        Storage::fake();

        $data = $this->data($this->faker);
        $carId = $data['id'];

        unset(
            $data['orders'][0]['customer'],
            $data['orders'][0]['dispatcher'],
            $data['orders'][0]['organization'],
            $data['orders'][0]['owner'],
            $data['orders'][0]['payer'],
        );

        $this->post(route('api.v1.history.car',[
            'carId' => $data['id']
        ]), $data, $this->headers())
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
        ;

        $model = CarItem::query()->where('car_uuid', $carId)->first();

        $this->assertNotEmpty($model->invoices);
        $this->assertNotEmpty($model->orders);
        $this->assertNull($model->orders->first()->customer);
        $this->assertNull($model->orders->first()->dispatcher);
        $this->assertNull($model->orders->first()->organization);
        $this->assertNull($model->orders->first()->owner);
        $this->assertNull($model->orders->first()->payer);
    }
}
