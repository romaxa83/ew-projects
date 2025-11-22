<?php

namespace Feature\Http\Api\V1\Inventories\Brand\Crud;

use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Inventories\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected BrandBuilder $brandBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected SeoBuilder $seoBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->brandBuilder = resolve(BrandBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $this->inventoryBuilder->brand($model)->create();

        /** @var $seo Seo */
        $seo = $this->seoBuilder->model($model)->create();

        $this->getJson(route('api.v1.inventories.brand.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'slug' => $model->slug,
                    'hasRelatedEntities' => true,
                    'seo' => [
                        'h1' => $seo->h1,
                        'title' => $seo->title,
                        'keywords' => $seo->keywords,
                        'desc' => $seo->desc,
                        'text' => $seo->text,
                        'image' =>  null,
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.inventories.brand.show', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.inventories.brand.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.brand.show', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.brand.show', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
