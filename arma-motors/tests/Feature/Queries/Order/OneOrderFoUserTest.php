<?php

namespace Tests\Feature\Queries\Order;

use App\Exceptions\ErrorsCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class OneOrderFoUserTest extends TestCase
{
    use DatabaseTransactions;
    use OrderBuilder;
    use UserBuilder;

    /** @test */
    public function success()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $order = $orderBuilder->asOne()->create();

        $response = $this->graphQL($this->getQueryStr($order->id));

        $responseData = $response->json('data.orderForUser');

        $this->assertArrayHasKey('id', $responseData);

        $this->assertEquals($order->id, $responseData['id']);
    }

    /** @test */
    public function not_auth()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();

        $order = $orderBuilder->asOne()->create();

        $response = $this->graphQL($this->getQueryStr($order->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr($id): string
    {
        return  sprintf('{
            orderForUser (id: "%s") {
                id
               }
            }',
            $id
        );
    }


}
