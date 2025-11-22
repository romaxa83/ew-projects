<?php

namespace Tests\Feature\Queries\Dealership;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DepartmentListTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.departments');

        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('active', $responseData[0]);
        $this->assertArrayHasKey('sort', $responseData[0]);
        $this->assertArrayHasKey('dealership', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['dealership']);
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            departments {
                id
                active
                sort
                dealership {
                    id
                }
               }
            }'
        );
    }
}




