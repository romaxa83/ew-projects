<?php

namespace Tests\Feature\Queries\Dealership;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class GetDealershipListTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.dealerships');

        $this->assertCount(3, $responseData);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('active', $responseData[0]);
        $this->assertArrayHasKey('sort', $responseData[0]);
        $this->assertArrayHasKey('location', $responseData[0]);
        $this->assertArrayHasKey('website', $responseData[0]);
        $this->assertArrayHasKey('current', $responseData[0]);
        $this->assertArrayHasKey('lang', $responseData[0]['current']);
        $this->assertArrayHasKey('name', $responseData[0]['current']);
        $this->assertArrayHasKey('text', $responseData[0]['current']);
        $this->assertArrayHasKey('address', $responseData[0]['current']);
        $this->assertArrayHasKey('translations', $responseData[0]);
        $this->assertCount(2, $responseData[0]['translations']);
        $this->assertArrayHasKey('lang', $responseData[0]['translations'][0]);
        $this->assertArrayHasKey('brand', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['brand']);
        $this->assertArrayHasKey('name', $responseData[0]['brand']);

        $this->assertArrayHasKey('departmentSales', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['departmentSales']);

        $this->assertArrayHasKey('departmentService', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['departmentService']);

        $this->assertArrayHasKey('departmentCash', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['departmentCash']);

        $this->assertArrayHasKey('departmentBody', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['departmentBody']);
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            dealerships {
                id
                active
                sort
                location
                website
                current {
                    lang
                    name
                    text
                    address
                }
                translations{
                    lang
                }
                brand {
                    id
                    name
                }
                departmentSales {
                    id
                }
                departmentService {
                    id
                }
                departmentCash {
                    id
                }
                departmentBody {
                    id
                }
               }
            }'
        );
    }
}




