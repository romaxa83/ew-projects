<?php

namespace Tests\Feature\Queries\Catalog\Service;

use App\Models\Catalogs\Service\InsuranceFranchise;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetInsuranceOneTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success_by_id()
    {
        $model = InsuranceFranchise::where('id', 1)->first();

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.insuranceFranchise');

        $this->assertEquals($model->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals($model->name, $responseData['name']);
    }

    /** @test */
    public function not_found_by_id()
    {
        $response = $this->graphQL($this->getQueryStr(999));

        $this->assertNull($response->json('data.insuranceFranchise'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            insuranceFranchise (id: %s) {
                id
                active
                name
               }
            }',
            $id
        );
    }
}

