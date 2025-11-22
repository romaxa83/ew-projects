<?php

namespace Tests\Feature\Queries\Catalog\Car;

use App\Exceptions\ErrorsCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class FuelListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.fuels');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('active', $responseData[0]);
        $this->assertArrayHasKey('current', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]['current']);
        $this->assertNotEmpty($responseData);
    }

    public function not_auth()
    {
        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            fuels {
                id
                active
                current {
                    name
                }
               }
            }'
        );
    }
}

