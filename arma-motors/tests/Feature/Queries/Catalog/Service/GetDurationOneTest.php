<?php

namespace Tests\Feature\Queries\Catalog\Service;

use App\Models\Catalogs\Service\Duration;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetDurationOneTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success_by_id()
    {
        $model = Duration::where('id', 1)->first();

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.duration');

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

        $this->assertNull($response->json('data.duration'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            duration (id: %s) {
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


