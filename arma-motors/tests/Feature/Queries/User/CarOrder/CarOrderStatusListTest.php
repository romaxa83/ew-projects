<?php

namespace Tests\Feature\Queries\User\CarOrder;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class CarOrderStatusListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.carOrderStatuses');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('sort', $responseData[0]);
        $this->assertArrayHasKey('current', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]['current']);
    }

    public static function getQueryStr(): string
    {
        return "{carOrderStatuses {
            id,
            sort
            current {name}
            }}";
    }
}
