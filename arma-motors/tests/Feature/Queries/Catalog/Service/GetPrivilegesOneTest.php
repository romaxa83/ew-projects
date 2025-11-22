<?php

namespace Tests\Feature\Queries\Catalog\Service;

use App\Models\Catalogs\Service\Privileges;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetPrivilegesOneTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success_by_id()
    {
        $model = Privileges::where('id', 1)->first();

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.privilege');

        $this->assertEquals($model->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('name', $responseData['current']);

        $this->assertEquals($model->current->name, $responseData['current']['name']);
    }

    /** @test */
    public function not_found_by_id()
    {
        $response = $this->graphQL($this->getQueryStr(999));

        $this->assertNull($response->json('data.privilege'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            privilege (id: %s) {
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
