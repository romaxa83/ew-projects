<?php

namespace Tests\Feature\Queries\Catalog\Region;

use App\Models\Catalogs\Region\City;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetOneCityTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success_by_id()
    {
        $random = City::query()->orderBy(\DB::raw('RAND()'))->first();

        $response = $this->graphQL($this->getQueryStr($random->id))
           ;

        $responseData = $response->json('data.city');

        $this->assertEquals($random->id, $responseData['id']);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('location', $responseData);
        $this->assertArrayHasKey('lat', $responseData['location']);
        $this->assertArrayHasKey('lon', $responseData['location']);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('translations', $responseData);
        $this->assertCount(2, $responseData['translations']);
        $this->assertArrayHasKey('name', $responseData['translations'][0]);
        $this->assertArrayHasKey('lang', $responseData['translations'][0]);
        $this->assertArrayHasKey('region', $responseData);
        $this->assertArrayHasKey('id', $responseData['region']);

    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            city (id: %s) {
                id
                active
                sort
                location
                current {
                    lang
                    name
                }
                translations {
                    lang
                    name
                }
                region {
                    id
                }
               }
            }',
        $id
        );
    }
}
