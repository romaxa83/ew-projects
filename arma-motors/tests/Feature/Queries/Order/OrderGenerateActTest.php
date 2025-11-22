<?php

namespace Tests\Feature\Queries\Order;

use App\Exceptions\ErrorsCode;
use App\Models\Order\Order;
use App\Services\AA\Commands\GetAct;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;
use Illuminate\Support\Facades\Storage;

class OrderGenerateActTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use OrderBuilder;

    /** @test */
    public function success()
    {
        ///app/storage/app/public/files/order/215/bill_ba8b6832-5742-11ec-8277-4cd98fc26f14.pdf
        // При запросе в АА, возвращаем заглушку
        \Http::fake(['*' => \Http::response([
            'data' => GetAct::testData()
        ])]);

        Storage::fake("public");

        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $order = $orderBuilder
            ->setUuid("ba8b6832-5742-11ec-8277-4cd98fc26f15")
            ->asOne()
            ->create();

        $order->refresh();

        $this->assertNull($order->actFile);

        $res = $this->graphQL(self::getQueryStr($order->id));

        $this->assertTrue(\Arr::get($res, "data.orderGenerateAct.status"));
        $this->assertEquals(
            \Arr::get($res, "data.orderGenerateAct.message"),
            __("message.order.generate act")
        );

        $order->refresh();

        $this->assertNotNull($order->actFile);
        $this->assertEquals($order->actFile->hash, $order->fileName(Order::FILE_ACT_TYPE));

        Storage::disk('public')->assertExists($order->storagePath(Order::FILE_ACT_TYPE));
    }

    /** @test */
    public function not_found_order()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $order = $orderBuilder
            ->setUuid("ba8b6832-5742-11ec-8277-4cd98fc26f15")
            ->asOne()
            ->create();

        $res = $this->graphQL(self::getQueryStr($order->id + 1));

        $this->assertEquals(\Arr::get($res, "errors.0.extensions.code"), ErrorsCode::NOT_FOUND);
        $this->assertEquals(
            \Arr::get($res, "errors.0.message"),
            __('error.not found model')
        );
    }

    /** @test */
    public function not_have_uuid_in_order()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $order = $orderBuilder
            ->asOne()
            ->create();

        $res = $this->graphQL(self::getQueryStr($order->id));

        $this->assertEquals(
            \Arr::get($res, "errors.0.message"),
            "Order [{$order->id}] has not uuid"
        );
    }

    public static function getQueryStr($id): string
    {
        return  sprintf('{
            orderGenerateAct(id: %s) {
                code
                status
                message
               }
            }',
            $id
        );
    }
}

