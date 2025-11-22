<?php

namespace Tests\Feature\Queries\Page;

use App\Models\Page\Page;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class PageOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_by_id()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $page = Page::where('id', 1)->first();

        $response = $this->graphQL($this->getQueryStrById($page->id))
            ->assertOk();

        $responseData = $response->json('data.page');

        $this->assertNotEmpty($responseData);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($page->id, $responseData['id']);

        $this->assertArrayHasKey('alias', $responseData);
        $this->assertEquals($page->alias, $responseData['alias']);
    }

    /** @test */
    public function success_by_alias()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $page = Page::where('id', 1)->first();

        $response = $this->graphQL($this->getQueryStrByAlias($page->alias))
            ->assertOk();

        $responseData = $response->json('data.page');

        $this->assertNotEmpty($responseData);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($page->id, $responseData['id']);

        $this->assertArrayHasKey('alias', $responseData);
        $this->assertEquals($page->alias, $responseData['alias']);
    }

    /** @test */
    public function not_found_by_alias()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrByAlias('wrong'));

        $this->assertNull($response->json('data.page'));
    }

    public function getQueryStrById($id): string
    {
        return  sprintf('{
            page(id: %s) {
                id
                alias
                current {
                    name
                    text
                    lang
                }
               }
            }',
        $id
        );
    }

    public function getQueryStrByAlias($alias): string
    {
        return  sprintf('{
            page(alias: "%s") {
                id
                alias
                current {
                    name
                    text
                    lang
                }
               }
            }',
            $alias
        );
    }
}
