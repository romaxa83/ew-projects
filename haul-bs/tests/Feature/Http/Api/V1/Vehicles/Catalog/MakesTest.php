<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Catalog;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Vehicles\MakeBuilder;
use Tests\TestCase;

class MakesTest extends TestCase
{
    use DatabaseTransactions;

    protected MakeBuilder $makeBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->makeBuilder = resolve(MakeBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->makeBuilder->name('aaaaa')->create();
        $m_2 = $this->makeBuilder->name('zzzbb')->create();
        $m_3 = $this->makeBuilder->name('zzzaa')->create();

        $this->getJson(route('api.v1.vehicles.makes', [
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
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.vehicles.makes', [
            'search' => 'zzz'
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.vehicles.makes'));

        self::assertUnauthenticatedMessage($res);
    }
}
