<?php

namespace Tests\Feature\Queries\Support;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\SupportBuilder;

class CategoryOneTest extends TestCase
{
    use DatabaseTransactions;
    use SupportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $category = $this->supportBuilder()->onlyCategory()->create();

        $response = $this->graphQL($this->getQueryStr($category->id))
            ->assertOk();

        $responseData = $response->json('data.category');

        $this->assertNotEmpty($responseData);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('lang', $responseData['current']);

        $this->assertEquals($category->id, $responseData['id']);
        $this->assertEquals($category->current->name, $responseData['current']['name']);
    }

    public function getQueryStr(string $id): string
    {
        return  sprintf('{
            category(id: %s) {
                id
                active
                sort
                current {
                    name
                    lang
                }
               }
            }',
        $id
        );
    }
}
