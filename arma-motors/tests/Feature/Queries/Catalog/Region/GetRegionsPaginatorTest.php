<?php

namespace Tests\Feature\Queries\Catalog\Region;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class GetRegionsPaginatorTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.regions');

        $this->assertCount(10, $responseData['data']);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('active', $responseData['data'][0]);
        $this->assertArrayHasKey('sort', $responseData['data'][0]);
        $this->assertArrayHasKey('current', $responseData['data'][0]);
        $this->assertArrayHasKey('lang', $responseData['data'][0]['current']);
        $this->assertArrayHasKey('name', $responseData['data'][0]['current']);
        $this->assertArrayHasKey('translations', $responseData['data'][0]);
        $this->assertCount(2, $responseData['data'][0]['translations']);
        $this->assertArrayHasKey('name', $responseData['data'][0]['translations'][0]);
        $this->assertArrayHasKey('lang', $responseData['data'][0]['translations'][0]);
        $this->assertArrayHasKey('cities', $responseData['data'][0]);
        $this->assertArrayHasKey('id', $responseData['data'][0]['cities'][0]);
        $this->assertArrayHasKey('current', $responseData['data'][0]['cities'][0]);
        $this->assertArrayHasKey('name', $responseData['data'][0]['cities'][0]['current']);

        $this->assertArrayHasKey('total', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('currentPage', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('hasMorePages', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('lastPage', $responseData['paginatorInfo']);
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            regions {
                data{
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
}



