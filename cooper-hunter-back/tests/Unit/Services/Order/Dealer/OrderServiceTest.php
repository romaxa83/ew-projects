<?php

namespace Tests\Unit\Services\Order\Dealer;

use App\Models\Orders\Dealer\Order;
use App\Services\Orders\Dealer\OrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyPriceBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected CompanyBuilder $companyBuilder;
    protected CompanyPriceBuilder $companyPriceBuilder;
    protected DealerBuilder $dealerBuilder;
    protected ProductBuilder $productBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->companyPriceBuilder = resolve(CompanyPriceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_upload(): void
    {
        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        /** @var $order Order */
        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();
        $product_4 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setProduct($product_1)->setCompany($company)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_2)->setCompany($company)->create();
        $price_3 = $this->companyPriceBuilder->setProduct($product_3)->setCompany($company)->create();

        $data = [
            [
                'id' => $product_1->id,
                'name' => $product_1->title,
                'brand' => $product_1->brand->name,
                'qty' => 3,
            ],
            [
                'id' => $product_2->id,
                'name' => $product_2->title,
                'brand' => $product_2->brand->name,
                'qty' => 5,
            ],
            [
                'id' => $product_3->id,
                'name' => $product_3->title,
                'brand' => $product_3->brand->name,
                'qty' => 0,
            ],
            [
                'id' => $product_4->id,
                'name' => $product_4->title,
                'brand' => $product_4->brand->name,
                'qty' => 3,
            ]
        ];

        $sheets = new SheetCollection([
            'Products' => $data
        ]);

        $filePath = (new FastExcel($sheets))->export('file.xlsx');
        /** @var $service OrderService */
        $service = app(OrderService::class);

        $order = $service->createFromFile($dealer, new UploadedFile($filePath, 'file.xlsx'));

        $this->assertEquals($order->dealer_id, $dealer->id);
        $this->assertNull($order->guid);
        $this->assertNull($order->shipping_address_id);
        $this->assertNull($order->payment_card_id);
        $this->assertNull($order->po);
        $this->assertNull($order->comment);
        $this->assertTrue($order->status->isDraft());
        $this->assertTrue($order->delivery_type->isNone());
        $this->assertTrue($order->payment_type->isNone());

        $this->assertCount(2, $order->items);
        $this->assertEquals($order->items[0]->product_id, data_get($data, '0.id'));
        $this->assertEquals($order->items[0]->qty, data_get($data, '0.qty'));
        $this->assertEquals($order->items[0]->price, $price_1->price);
        $this->assertEquals($order->items[0]->discount, 0);

        $this->assertEquals($order->items[1]->product_id, data_get($data, '1.id'));
        $this->assertEquals($order->items[1]->qty, data_get($data, '1.qty'));
        $this->assertEquals($order->items[1]->price, $price_2->price);
        $this->assertEquals($order->items[1]->discount, 0);

        unlink($filePath);
    }

    /** @test */
    public function fail_empty_file(): void
    {
        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $data = [];

        $sheets = new SheetCollection([
            'Products' => $data
        ]);

        $filePath = (new FastExcel($sheets))->export('file.xlsx');
        $service = app(OrderService::class);

        $this->expectException(\Core\Exceptions\TranslatedException::class);
        $this->expectExceptionMessage(__('messages.dealer.order.file.not items for create'));

        /** @var $service OrderService */
        $service->createFromFile($dealer, new UploadedFile($filePath, 'file.xlsx'));

        unlink($filePath);
    }

    /** @test */
    public function fail_all_product_without_qty(): void
    {
        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        /** @var $order Order */
        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setProduct($product_1)->setCompany($company)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_2)->setCompany($company)->create();

        $data = [
            [
                'id' => $product_1->id,
                'name' => $product_1->title,
                'brand' => $product_1->brand->name,
                'qty' => 0,
            ],
            [
                'id' => $product_2->id,
                'name' => $product_2->title,
                'brand' => $product_2->brand->name,
                'qty' => 0,
            ],
        ];

        $sheets = new SheetCollection([
            'Products' => $data
        ]);

        $filePath = (new FastExcel($sheets))->export('file.xlsx');
        /** @var $service OrderService */
        $service = app(OrderService::class);

        $this->expectException(\Core\Exceptions\TranslatedException::class);
        $this->expectExceptionMessage(__('messages.dealer.order.file.not items for create'));

        /** @var $service OrderService */
        $service->createFromFile($dealer, new UploadedFile($filePath, 'file.xlsx'));

        unlink($filePath);
    }

    /** @test */
    public function fail_all_product_without_price(): void
    {
        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        /** @var $order Order */
        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $data = [
            [
                'id' => $product_1->id,
                'name' => $product_1->title,
                'brand' => $product_1->brand->name,
                'qty' => 4,
            ],
            [
                'id' => $product_2->id,
                'name' => $product_2->title,
                'brand' => $product_2->brand->name,
                'qty' => 1,
            ],
        ];

        $sheets = new SheetCollection([
            'Products' => $data
        ]);

        $filePath = (new FastExcel($sheets))->export('file.xlsx');
        /** @var $service OrderService */
        $service = app(OrderService::class);

        $this->expectException(\Core\Exceptions\TranslatedException::class);
        $this->expectExceptionMessage(__('messages.dealer.order.file.not items for create'));

        /** @var $service OrderService */
        $service->createFromFile($dealer, new UploadedFile($filePath, 'file.xlsx'));

        unlink($filePath);
    }
}
