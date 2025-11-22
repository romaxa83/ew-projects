<?php

namespace Feature\Http\Api\V1\Inventories\Feature\EComm;

use App\Models\Inventories\Features\Feature;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $featureValueBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Feature */
        $m_1 = $this->featureBuilder->create();

        $this->featureValueBuilder->feature($m_1)->create();
        $this->featureValueBuilder->feature($m_1)->create();
        $this->featureValueBuilder->feature($m_1)->create();

        $this->getJson(route('api.v1.e_comm.features'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'position',
                        'active',
                        'multiple',
                        'created_at',
                        'updated_at',
                        'values' => [
                            [
                                'id',
                                'name',
                                'slug',
                                'position',
                                'active',
                                'created_at',
                                'updated_at',
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(3, 'data.0.values')
        ;
    }

    /** @test */
    public function success_list_more()
    {
        $this->loginUserAsSuperAdmin();
        Feature::factory()->count(100)->create();

        $this->getJson(route('api.v1.e_comm.features'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonCount(100, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.e_comm.features'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function wrong_token()
    {
        $res = $this->getJson(route('api.v1.e_comm.features'), [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
