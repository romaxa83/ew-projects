<?php

namespace Tests\Feature\Queries\Catalog\Calc;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\CalcCatalogBuilder;

class WorkOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CalcCatalogBuilder;

    /** @test */
    public function success_by_id()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $calcBuilder = $this->calcCatalogBuilder();
        $model = $calcBuilder->createWork();
        $model->brands()->attach([1,2]);

        $this->assertNotNull($model);

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.work');

        $this->assertEquals($model->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('brands', $responseData);
        $this->assertIsArray($responseData['brands']);
        $this->assertCount(2, $responseData['brands']);

        $this->assertEquals($model->current->name, $responseData['current']['name']);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr(999));
        $this->assertNull($response->json('data.work'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            work (id: %s) {
                id
                active
                current {
                    lang
                    name
                }
                brands {
                    id
                }
               }
            }',
            $id
        );
    }
}
