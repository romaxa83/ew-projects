<?php

namespace Tests\Feature\Queries\Catalog\Calc;

use App\Models\Catalogs\Calc\SparesGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class SparesGroupOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success_by_id()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $model = SparesGroup::where('id', 1)->first();

        $this->assertNotNull($model);

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.sparesGroup');

        $this->assertEquals($model->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('unit', $responseData['current']);

        $this->assertEquals($model->current->name, $responseData['current']['name']);
        $this->assertEquals($model->current->unit, $responseData['current']['unit']);
        $this->assertNull($responseData['brand']);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr(999));
        $this->assertNull($response->json('data.sparesGroup'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            sparesGroup (id: %s) {
                id
                active
                type
                current {
                    lang
                    name
                    unit
                }
                brand {
                    id
                }
               }
            }',
            $id
        );
    }
}
