<?php

namespace Feature\Http\Api\V1\Inventories\Category\Crud;

use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected SeoBuilder $seoBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryBuilder = resolve(CategoryBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $menu_img = UploadedFile::fake()->image('menu.png');
        /** @var $model Category */
        $model = $this->categoryBuilder->menuImg($menu_img)->create();

        $this->inventoryBuilder->category($model)->create();
        /** @var $seo Seo */
        $seo = $this->seoBuilder->model($model)->create();

        $this->getJson(route('api.v1.inventories.category.show', ['id' => $model->id]))
            ->assertJsonStructure([
                'data' => [
                    'menu_image' => [
                        'id',
                        'original',
                        'sm',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'slug' => $model->slug,
                    'desc' => $model->desc,
                    'parent_id' => $model->parent_id,
                    'display_menu' => $model->display_menu,
                    'position' => $model->position,
                    'hasRelatedEntities' => true,
                    'seo' => [
                        'h1' => $seo->h1,
                        'title' => $seo->title,
                        'keywords' => $seo->keywords,
                        'desc' => $seo->desc,
                        'text' => $seo->text,
                        'image' =>  null,
                    ],
                    'header_image' => null,
                    'mobile_image' => null,
                    'menu_image' => [
                        'id' => $model->getMenuImg()->id
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.inventories.category.show', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.inventories.category.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.category.show', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.category.show', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
