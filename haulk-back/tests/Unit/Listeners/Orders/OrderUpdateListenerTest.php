<?php

namespace Tests\Unit\Listeners\Orders;

use App\Broadcasting\Events\Offers\ReleaseOfferBroadcast;
use App\Broadcasting\Events\Offers\TakenOfferBroadcast;
use App\Models\Orders\Order;
use App\Models\Users\User;
use App\Services\Events\EventService;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderUpdateListenerTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_has_release_offer_broadcast(): void
    {
        $login = $this->loginAsCarrierDispatcher();

        $user = User::factory()->create();

        $dispatcher = $this->dispatcherFactory();

        Event::fake();

        $order = new Order();
        $order->id = 1;
        $order->carrier_id = 1;
        $order->user_id = $user->id;
        $order->load_id = 'load_1';
        $order->dispatcher_id = $dispatcher->id;
        $order->status = Order::STATUS_NEW;
        $order->public_token = hash('sha256', Str::random(60));
        $order->save();
        $order->refresh();

        $order->dispatcher_id = null;
        $order->save();

        EventService::order($order)->user($login)->update()->broadcast();

        Event::assertDispatched(ReleaseOfferBroadcast::class);
    }

    public function test_it_has_taken_offer_broadcast(): void
    {
        $login = $this->loginAsCarrierDispatcher();

        $dispatcher = $this->dispatcherFactory();

        Event::fake();

        $order = new Order();
        $order->id = 1;
        $order->carrier_id = 1;
        $order->load_id = 'load_1';
        $order->user_id = 1;
        $order->status = Order::STATUS_NEW;
        $order->public_token = hash('sha256', Str::random(60));
        $order->save();
        $order->refresh();

        $order->dispatcher_id = $dispatcher->id;
        $order->save();

        EventService::order($order)->user($login)->update()->broadcast();

        Event::assertDispatched(TakenOfferBroadcast::class);
    }
}
