<?php

namespace Tests\Feature\Queries\Catalog\Region;

use App\Models\Catalogs\Region\Region;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetOneRegionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success_by_id()
    {
        $random = Region::query()->orderBy(\DB::raw('RAND()'))->first();

        $response = $this->graphQL($this->getQueryStr($random->id))
            ->assertOk();

        $responseData = $response->json('data.region');

        $this->assertEquals($random->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('translations', $responseData);
        $this->assertCount(2, $responseData['translations']);
        $this->assertArrayHasKey('name', $responseData['translations'][0]);
        $this->assertArrayHasKey('lang', $responseData['translations'][0]);
        $this->assertArrayHasKey('cities', $responseData);
        $this->assertArrayHasKey('id', $responseData['cities'][0]);

    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            region (id: %s) {
                id
                active
                sort
                current {
                    lang
                    name
                }
                translations {
                    lang
                    name
                }
                cities {
                    id
                }
               }
            }',
            $id
        );
    }
}

