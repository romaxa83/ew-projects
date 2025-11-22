<?php

namespace Tests\Feature\Queries\Support;

use App\Models\Support\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\SupportBuilder;

class CategoryListTest extends TestCase
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
        Category::factory()->count(5)->create();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.categories');

        $this->assertNotEmpty($responseData);
        $this->assertCount(5, $responseData);

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('active', $responseData[0]);
        $this->assertArrayHasKey('sort', $responseData[0]);
    }

    /** @test */
    public function success_with_active()
    {
        Category::factory()->count(5)->create(['active' => true]);
        Category::factory()->count(3)->create(['active' => false]);

        $response = $this->graphQL($this->getQueryStrActive(true));
        $this->assertCount(5, $response->json('data.categories'));

        $response = $this->graphQL($this->getQueryStrActive(false));
        $this->assertCount(3, $response->json('data.categories'));

        $response = $this->graphQL($this->getQueryStr());
        $this->assertCount(8, $response->json('data.categories'));
    }

    public function getQueryStr(): string
    {
        return  sprintf('{
            categories {
                id
                active
                sort
               }
            }'
        );
    }

    public function getQueryStrActive($active): string
    {
        $act = $active == true ? 'true' : 'false' ;
        return  sprintf('{
            categories (active: %s) {
                id
                active
                sort
               }
            }',
        $act
        );
    }
}

