<?php

namespace Tests\Feature\Queries\Catalog\Calc;

use App\Models\Catalogs\Calc\Spares;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\CalcCatalogBuilder;

class SparesPaginatorTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CalcCatalogBuilder;

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.spares');

        $this->assertNotEmpty($responseData['data']);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('active', $responseData['data'][0]);
        $this->assertArrayHasKey('sort', $responseData['data'][0]);
        $this->assertArrayHasKey('type', $responseData['data'][0]);
        $this->assertArrayHasKey('article', $responseData['data'][0]);
        $this->assertArrayHasKey('name', $responseData['data'][0]);
        $this->assertArrayHasKey('qty', $responseData['data'][0]);
        $this->assertArrayHasKey('price', $responseData['data'][0]);
    }

    /** @test */
    public function get_by_type()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrByType(Spares::TYPE_VOLVO));
        $totalVolvo = $response->json('data.spares.paginatorInfo.total');

        $response = $this->graphQL($this->getQueryStrByType(Spares::TYPE_MITSUBISHI));
        $totalMits = $response->json('data.spares.paginatorInfo.total');

        $this->assertNotEquals($totalVolvo , $totalMits);
    }

    /** @test */
    public function search_by_article()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $article = 'MD35';
        $sparesCount = Spares::query()->where('article','like', $article . '%')->count();

        $response = $this->graphQL($this->getQueryStrByArticle($article));

        $this->assertEquals($sparesCount, $response->json('data.spares.paginatorInfo.total'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            spares {
                data {
                    id
                    active
                    sort
                    type
                    article
                    name
                    qty
                    price
                }
               }
            }'
        );
    }

    public static function getQueryStrByType($type): string
    {
        return  sprintf('{
            spares (type: %s) {
                data {
                    id
                    active
                    sort
                    type
                    article
                    name
                    qty
                    price
                }
                paginatorInfo {
                    total
                }
               }
            }',
        $type
        );
    }

    public static function getQueryStrByArticle($article): string
    {
        return  sprintf('{
            spares (article: "%s") {
                data {
                    article
                    name
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $article
        );
    }
}
