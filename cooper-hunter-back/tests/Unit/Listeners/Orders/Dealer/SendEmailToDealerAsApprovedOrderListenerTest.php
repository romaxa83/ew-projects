<?php

namespace Tests\Unit\Listeners\Orders\Dealer;

use App\Events\Orders\Dealer\ApprovedOrderEvent;
use App\Listeners\Orders\Dealer\SendEmailToDealerAsApprovedOrderListener;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use App\Notifications\Orders\Dealer\SendApprovedOrderToDealerNotification;
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

class SendEmailToDealerAsApprovedOrderListenerTest extends TestCase
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
        $this->productBuilder = resolve(ProductBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        Notification::fake();
        /** @var $company Company */
        $company = $this->companyBuilder->withManager()->withCommercialManager()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $product_1 = $this->productBuilder->create();

        $order_item_1 = $this->orderItemBuilder->setOrder($order)->setProduct($product_1)->create();

        $event = new ApprovedOrderEvent($order, false);
        $listener = resolve(SendEmailToDealerAsApprovedOrderListener::class);
        $listener->handle($event);

        Notification::assertSentTo(new AnonymousNotifiable(), SendApprovedOrderToDealerNotification::class,
            function ($notification, $channels, $notifiable) use ($dealer) {
                return $notifiable->routes['mail'] == $dealer->email->getValue();
            }
        );
    }
}
