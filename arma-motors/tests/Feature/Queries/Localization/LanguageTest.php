<?php

namespace Tests\Feature\Queries\Localization;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class LanguageTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function languages()
    {
        $query = sprintf('{
            languages {
                name
                slug
                default
               }
            }'
        );

        $response = $this->graphQL($query)
            ->assertOk();

        $responseData = $response->json('data.languages');

        $this->assertCount(2, $responseData);
        $this->assertArrayHasKey('name', $responseData[0]);
        $this->assertArrayHasKey('slug', $responseData[0]);
        $this->assertArrayHasKey('default', $responseData[0]);
    }
}

