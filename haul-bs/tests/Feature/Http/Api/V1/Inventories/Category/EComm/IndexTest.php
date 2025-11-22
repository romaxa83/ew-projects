<?php

namespace Feature\Http\Api\V1\Inventories\Category\EComm;

use App\Models\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;
    protected SeoBuilder $seoBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryBuilder = resolve(CategoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $menu_img = UploadedFile::fake()->image('menu.png');

        /** @var $m_1 Category */
        $m_1 = $this->categoryBuilder->menuImg($menu_img)->create();

        $seo = $this->seoBuilder->model($m_1)->create();

        $this->getJson(route('api.v1.e_comm.categories'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'desc',
                        'parent_id',
                        'position',
                        'active',
                        'display_menu',
                        'created_at',
                        'updated_at',
                        'header_image',
                        'mobile_image',
                        'menu_image' => [
                            'id',
                            'name',
                            'file_name',
                            'mime_type',
                            'size',
                            'original',
                            'sm',
                        ],
                        'seo' => [
                            'h1',
                            'title',
                            'keywords',
                            'desc',
                            'text',
                            'image',
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_more()
    {
        $this->loginUserAsSuperAdmin();
        Category::factory()->count(100)->create();

        $this->getJson(route('api.v1.e_comm.categories'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonCount(100, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.e_comm.categories'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function wrong_token()
    {
        $res = $this->getJson(route('api.v1.e_comm.categories'), [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
