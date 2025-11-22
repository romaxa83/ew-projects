<?php

namespace Tests\Feature\Http\Api\V1\Order;

use App\Exceptions\ErrorsCode;
use App\Models\Order\Order;
use App\Services\AA\Commands\GetInvoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\Builders\AAPostBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class OrderBillTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use OrderBuilder;
    use CarBuilder;
    use AAPostBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->armaAuth();
    }

    public function headers()
    {
        return [
            'Authorization' => 'Basic d2V6b20tYXBpOndlem9tLWFwaQ=='
        ];
    }

    /** @test */
    public function success()
    {
        Storage::fake("public");
        $uuid = '3c13fafb-79d6-11ec-8277-4cd98fc26f14';
        $order = $this->orderBuilder()
            ->setUuid($uuid)
            ->asOne()->create();

        $order->refresh();

        $this->assertNull($order->billFile);

        $this->post(route('api.v1.order.bill', ["orderId" => $uuid]),
            GetInvoice::testData(),
            $this->headers()
        )
            ->assertOk()
            ->assertJson([
                "success" => true,
                "data" => []
            ])
        ;

        $order->refresh();

        $this->assertNotNull($order->billFile);
        $this->assertEquals($order->billFile->hash, $order->fileName(Order::FILE_BILL_TYPE));

        Storage::disk('public')->assertExists($order->storagePath(Order::FILE_BILL_TYPE));
    }

    /** @test */
    public function not_found_order()
    {
        $uuid = '3c13fafb-79d6-11ec-8277-4cd98fc26f14';

        $this->post(
            route('api.v1.order.bill', ["orderId" => $uuid]),
            GetInvoice::testData(),
            $this->headers()
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "data" => "Not found order by [orderId - {$uuid}]"
            ])
        ;
    }

    /** @test */
    public function wrong_auth_token()
    {
        $uuid = '3c13fafb-79d6-11ec-8277-4cd98fc26f14';
        $this->orderBuilder()
            ->setUuid($uuid)
            ->asOne()->create();

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $this->post(
            route('api.v1.order.bill', ["orderId" => $uuid]),
            GetInvoice::testData(),
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH)
            ->assertJson([
                "success" => false,
                "data" => 'Bad authorization token',
            ]);
    }

    /** @test */
    public function without_auth_token()
    {
        $uuid = '3c13fafb-79d6-11ec-8277-4cd98fc26f14';
        $this->orderBuilder()
            ->setUuid($uuid)
            ->asOne()->create();

        $this->post(
            route('api.v1.order.bill', ["orderId" => $uuid]),
            GetInvoice::testData(),
            []
        )
            ->assertStatus(ErrorsCode::NOT_AUTH)
            ->assertJson([
                "success" => false,
                "data" => 'Missing authorization header',
            ])
        ;
    }
}





