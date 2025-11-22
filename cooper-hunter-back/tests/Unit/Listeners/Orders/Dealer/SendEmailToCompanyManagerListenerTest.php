<?php

namespace Tests\Unit\Listeners\Orders\Dealer;

use App\Enums\Categories\CategoryTypeEnum;
use App\Events\Orders\Dealer\CheckoutOrderEvent;
use App\Listeners\Orders\Dealer\SendEmailToCompanyManagerListener;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use App\Notifications\Orders\Dealer\SendOrderToCommercialManagerNotification;
use App\Notifications\Orders\Dealer\SendOrderToManagerNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Catalog\CategoryBuilder;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class SendEmailToCompanyManagerListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected CompanyBuilder $companyBuilder;
    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $orderItemBuilder;
    protected DealerBuilder $dealerBuilder;
    protected CategoryBuilder $categoryBuilder;
    protected ProductBuilder $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->orderItemBuilder = resolve(ItemBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->categoryBuilder = resolve(CategoryBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
    }

    /** @test */
    public function success_send_manager_and_commercial_manager()
    {
        Notification::fake();
        /** @var $company Company */
        $company = $this->companyBuilder->withManager()->withCommercialManager()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $cat_1 = $this->categoryBuilder->setType(CategoryTypeEnum::COMMERCIAL())->create();
        $cat_2 = $this->categoryBuilder->setParentId($cat_1->id)->create();
        $cat_3 = $this->categoryBuilder->setParentId($cat_1->id)->create();
        $cat_4 = $this->categoryBuilder->setParentId($cat_2->id)->create();
        $cat_5 = $this->categoryBuilder->create();
        $cat_6 = $this->categoryBuilder->setParentId($cat_5->id)->create();

        $product_1 = $this->productBuilder->setCategoryId($cat_1->id)->create();
        $product_2 = $this->productBuilder->setCategoryId($cat_3->id)->create();
        $product_3 = $this->productBuilder->create();
        $product_4 = $this->productBuilder->create();

        $order_item_1 = $this->orderItemBuilder->setOrder($order)->setProduct($product_1)->create();
        $order_item_2 = $this->orderItemBuilder->setOrder($order)->setProduct($product_2)->create();
        $order_item_3 = $this->orderItemBuilder->setOrder($order)->setProduct($product_3)->create();
        $order_item_4 = $this->orderItemBuilder->setOrder($order)->setProduct($product_4)->create();

        $event = new CheckoutOrderEvent($order);
        $listener = resolve(SendEmailToCompanyManagerListener::class);
        $listener->handle($event);

        Notification::assertSentTo(new AnonymousNotifiable(), SendOrderToManagerNotification::class,
            function ($notification, $channels, $notifiable) use ($company) {
                return $notifiable->routes['mail'] == $company->manager->email->getValue();
            }
        );

        Notification::assertSentTo(new AnonymousNotifiable(), SendOrderToCommercialManagerNotification::class,
            function ($notification, $channels, $notifiable) use ($company) {
                return $notifiable->routes['mail'] == $company->commercialManager->email->getValue();
            }
        );
    }

    /** @test */
    public function success_send_only_manager()
    {
        Notification::fake();
        /** @var $company Company */
        $company = $this->companyBuilder->withManager()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $cat_1 = $this->categoryBuilder->create();
        $cat_2 = $this->categoryBuilder->create();

        $product_1 = $this->productBuilder->setCategoryId($cat_1->id)->create();
        $product_2 = $this->productBuilder->setCategoryId($cat_2->id)->create();
        $product_3 = $this->productBuilder->setCategoryId($cat_2->id)->create();


        $order_item_1 = $this->orderItemBuilder->setOrder($order)->setProduct($product_1)->create();
        $order_item_2 = $this->orderItemBuilder->setOrder($order)->setProduct($product_2)->create();
        $order_item_3 = $this->orderItemBuilder->setOrder($order)->setProduct($product_3)->create();

        $event = new CheckoutOrderEvent($order);
        $listener = resolve(SendEmailToCompanyManagerListener::class);
        $listener->handle($event);

        Notification::assertSentTo(new AnonymousNotifiable(), SendOrderToManagerNotification::class,
            function ($notification, $channels, $notifiable) use ($company) {
                return $notifiable->routes['mail'] == $company->manager->email->getValue();
            }
        );

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendOrderToCommercialManagerNotification::class);
    }

    /** @test */
    public function success_send_only_commercial_manager()
    {
        Notification::fake();
        /** @var $company Company */
        $company = $this->companyBuilder->withManager()->withCommercialManager()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $cat_1 = $this->categoryBuilder->setType(CategoryTypeEnum::COMMERCIAL())->create();
        $cat_2 = $this->categoryBuilder->setParentId($cat_1->id)->create();
        $cat_3 = $this->categoryBuilder->setParentId($cat_1->id)->create();
        $cat_4 = $this->categoryBuilder->setParentId($cat_2->id)->create();
        $cat_5 = $this->categoryBuilder->create();
        $cat_6 = $this->categoryBuilder->setParentId($cat_5->id)->create();

        $product_1 = $this->productBuilder->setCategoryId($cat_1->id)->create();
        $product_2 = $this->productBuilder->setCategoryId($cat_3->id)->create();

        $order_item_1 = $this->orderItemBuilder->setOrder($order)->setProduct($product_1)->create();
        $order_item_2 = $this->orderItemBuilder->setOrder($order)->setProduct($product_2)->create();

        $event = new CheckoutOrderEvent($order);
        $listener = resolve(SendEmailToCompanyManagerListener::class);
        $listener->handle($event);

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendOrderToManagerNotification::class);

        Notification::assertSentTo(new AnonymousNotifiable(), SendOrderToCommercialManagerNotification::class,
            function ($notification, $channels, $notifiable) use ($company) {
                return $notifiable->routes['mail'] == $company->commercialManager->email->getValue();
            }
        );
    }

    /** @test */
    public function not_manager()
    {
        Notification::fake();
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $event = new CheckoutOrderEvent($order);
        $listener = resolve(SendEmailToCompanyManagerListener::class);
        $listener->handle($event);

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendOrderToManagerNotification::class);
    }
}
