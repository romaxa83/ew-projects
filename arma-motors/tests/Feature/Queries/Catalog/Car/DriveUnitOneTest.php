<?php

namespace Tests\Feature\Queries\Catalog\Car;

use App\Models\Catalogs\Car\DriveUnit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class DriveUnitOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success_by_id()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $model = DriveUnit::where('id', 1)->first();

        $this->assertNotNull($model);

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.driveUnit');

        $this->assertEquals($model->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals($model->name, $responseData['name']);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr(999));
        $this->assertNull($response->json('data.driveUnit'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            driveUnit (id: %s) {
                id
                active
                name
               }
            }',
            $id
        );
    }
}


