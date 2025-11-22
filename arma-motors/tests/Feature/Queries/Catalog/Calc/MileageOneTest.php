<?php

namespace Tests\Feature\Queries\Catalog\Calc;

use App\Models\Catalogs\Calc\Mileage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\CalcCatalogBuilder;

class MileageOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CalcCatalogBuilder;

    /** @test */
    public function success_by_id()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $model = Mileage::where('id', 1)->first();
        $model->brands()->attach([1,2]);

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.mileage');

        $this->assertEquals($model->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('value', $responseData);
        $this->assertArrayHasKey('brands', $responseData);
        $this->assertIsArray($responseData['brands']);
        $this->assertCount(2, $responseData['brands']);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr(999));
        $this->assertNull($response->json('data.mileage'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            mileage (id: %s) {
                id
                active
                value
                brands {
                    id
                }
               }
            }',
            $id
        );
    }
}
