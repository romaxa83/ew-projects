<?php

namespace Feature\Http\Api\V1\Orders\Parts\Crud;

use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\ShippingMethod;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_create()
    {
        $this->loginUserAsSuperAdmin();

        $id = $this->postJson(route('api.v1.orders.parts'))
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'status_changed_at',
                ]
            ])
            ->assertJson([
                'data' => [
                    'customer' => null,
                    'sales_manager' => null,
                    'status' => OrderStatus::New->value,
                    'total_amount' => null,
                    'paid_at' => null,
                    'delivery_address' => null,
                    'billing_address' => null,
                    'source' => OrderSource::BS(),
                    'is_refunded' => false,
                    'is_draft' => true,
                    'delivery_type' => null,
                    'items' => [],
                    'shipping_methods' => [
                        [
                            'name' => ShippingMethod::UPS_Standard(),
                            'cost' => 0,
                            'terms' => null,
                        ]
                    ],
                    'payment' => null,
                    'delivery_cost' => 0
                ],
            ])
            ->assertJsonCount(0,'data.items')
            ->assertJsonCount(1,'data.shipping_methods')
            ->assertJsonCount(0,'data.deliveries')
            ->json('data.id')
        ;

        $model = Order::find($id);
        $this->assertEmpty($model->histories);

        $this->assertNull($model->delivered_at);
        $this->assertNull($model->past_due_at);
    }

    /** @test */
    public function success_create_as_sales_manager()
    {
        $sales = $this->loginUserAsSalesManager();

        $data = $this->data;

        $this->postJson(route('api.v1.orders.parts'), $data)
            ->assertJsonStructure([
                'data' => [
                    'sales_manager' => [
                        'id',
                        'first_name',
                        'last_name',
                        'full_name',
                        'email',
                        'phone',
                        'phone_extension',
                        'phones',
                        'status',
                        'deleted_at',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'sales_manager' => [
                        'id' => $sales->id,
                    ],
                    'status' => OrderStatus::New(),
                ],
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.parts'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.parts'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
