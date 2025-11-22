<?php

namespace Tests\Feature\Queries\Catalog\Region;

use App\Models\Catalogs\Region\City;
use App\Models\Catalogs\Region\Region;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class GetCitiesPaginatorTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.cities');

        $this->assertCount(10, $responseData['data']);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('active', $responseData['data'][0]);
        $this->assertArrayHasKey('sort', $responseData['data'][0]);
        $this->assertArrayHasKey('location', $responseData['data'][0]);
        $this->assertArrayHasKey('lat', $responseData['data'][0]['location']);
        $this->assertArrayHasKey('lon', $responseData['data'][0]['location']);
        $this->assertArrayHasKey('current', $responseData['data'][0]);
        $this->assertArrayHasKey('lang', $responseData['data'][0]['current']);
        $this->assertArrayHasKey('name', $responseData['data'][0]['current']);
        $this->assertArrayHasKey('translations', $responseData['data'][0]);
        $this->assertCount(2, $responseData['data'][0]['translations']);
        $this->assertArrayHasKey('name', $responseData['data'][0]['translations'][0]);
        $this->assertArrayHasKey('lang', $responseData['data'][0]['translations'][0]);
        $this->assertArrayHasKey('region', $responseData['data'][0]);
        $this->assertArrayHasKey('id', $responseData['data'][0]['region']);
        $this->assertArrayHasKey('current', $responseData['data'][0]['region']);
        $this->assertArrayHasKey('name', $responseData['data'][0]['region']['current']);

        $this->assertArrayHasKey('total', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('currentPage', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('hasMorePages', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('lastPage', $responseData['paginatorInfo']);

        $this->assertEquals($responseData['data'][0]['current']['lang'], \App::getLocale());
    }

    /** @test */
    public function get_by_region()
    {
        $region = Region::find(1);
        $total = City::where('region_id', $region->id)->count();

        $response = $this->graphQL($this->getQueryStrByRegion($region->id))
            ->assertOk();

        $this->assertEquals($total, $response->json('data.cities.paginatorInfo.total'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            cities {
                data{
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
                        current {
                            name
                        }
                    }
                }
                paginatorInfo {
                    total
                    count
                    currentPage
                    hasMorePages
                    lastPage
                }
               }
            }'
        );
    }

    public static function getQueryStrByRegion($regionId): string
    {
        return  sprintf('{
            cities (regionId: %s) {
                data{
                    id
                }
                paginatorInfo {
                    total
                }
               }
            }',
        $regionId
        );
    }
}
