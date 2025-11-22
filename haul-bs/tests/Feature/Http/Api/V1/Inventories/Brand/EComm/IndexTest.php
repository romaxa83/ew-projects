<?php

namespace Feature\Http\Api\V1\Inventories\Brand\EComm;

use App\Models\Inventories\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected BrandBuilder $brandBuilder;
    protected SeoBuilder $seoBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->brandBuilder = resolve(BrandBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Brand */
        $m_1 = $this->brandBuilder->create();

        $seo = $this->seoBuilder->model($m_1)->create();

        $this->getJson(route('api.v1.e_comm.brands'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'created_at',
                        'updated_at',
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
        Brand::factory()->count(100)->create();

        $this->getJson(route('api.v1.e_comm.brands'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonCount(100, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.e_comm.brands'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function wrong_token()
    {
        $res = $this->getJson(route('api.v1.e_comm.brands'), [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
