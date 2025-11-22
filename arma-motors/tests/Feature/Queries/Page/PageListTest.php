<?php

namespace Tests\Feature\Queries\Page;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class PageListTest extends TestCase
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
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.pages');

        $this->assertNotEmpty($responseData);

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('alias', $responseData[0]);
        $this->assertArrayHasKey('current', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]['current']);
        $this->assertArrayHasKey('text', $responseData[0]['current']);
        $this->assertArrayHasKey('lang', $responseData[0]['current']);
    }

    public function getQueryStr(): string
    {
        return  sprintf('{
            pages {
                id
                alias
                current {
                    name
                    text
                    lang
                }
               }
            }'
        );
    }
}
