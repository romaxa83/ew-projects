<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Catalog;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Vehicles\MakeBuilder;
use Tests\Builders\Vehicles\ModelBuilder;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use DatabaseTransactions;

    protected MakeBuilder $makeBuilder;
    protected ModelBuilder $modelBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->makeBuilder = resolve(MakeBuilder::class);
        $this->modelBuilder = resolve(ModelBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->modelBuilder->name('aaaaa')->create();
        $m_2 = $this->modelBuilder->name('zzzbb')->create();
        $m_3 = $this->modelBuilder->name('zzzaa')->create();

        $this->getJson(route('api.v1.vehicles.models', [
            'search' => 'zzz'
        ]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id,],
                    ['id' => $m_2->id,]
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_filter_make_name()
    {
        $this->loginUserAsSuperAdmin();

        $make_1 = $this->makeBuilder->name('aaaaa')->create();
        $model_1 = $this->modelBuilder->make($make_1)->name('seria')->create();

        $make_2 = $this->makeBuilder->name('zzzbb')->create();
        $model_2 = $this->modelBuilder->make($make_2)->name('seria')->create();

        $make_3 = $this->makeBuilder->name('zzzaa')->create();
        $model_3 = $this->modelBuilder->make($make_3)->name('seria')->create();

        $this->getJson(route('api.v1.vehicles.models', [
            'make_name' => 'aaa'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $model_1->id,],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.vehicles.models', [
            'search' => 'zzz'
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.vehicles.models'));

        self::assertUnauthenticatedMessage($res);
    }
}
