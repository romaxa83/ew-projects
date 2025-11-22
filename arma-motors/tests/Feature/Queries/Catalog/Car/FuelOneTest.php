<?php

namespace Tests\Feature\Queries\Catalog\Car;

use App\Models\Catalogs\Car\Fuel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class FuelOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success_by_id()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $model = Fuel::where('id', 1)->first();

        $this->assertNotNull($model);

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.fuel');

        $this->assertEquals($model->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('name', $responseData['current']);

        $this->assertEquals($model->current->name, $responseData['current']['name']);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr(999));
        $this->assertNull($response->json('data.fuel'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            fuel (id: %s) {
                id
                active
                current {
                    lang
                    name
                }
               }
            }',
            $id
        );
    }
}

