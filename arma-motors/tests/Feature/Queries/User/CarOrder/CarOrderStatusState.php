<?php

namespace Tests\Feature\Queries\User\CarOrder;

use App\Exceptions\ErrorsCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class CarOrderStatusState extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success_by_id()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.carOrderStatusState');

        $this->assertArrayHasKey('key', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]);
        $this->assertCount(4, $responseData);
    }

    /** @test */
    public function not_auth()
    {
        $this->adminBuilder()->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            carOrderStatusState {
                key
                name
               }
            }'
        );
    }
}


