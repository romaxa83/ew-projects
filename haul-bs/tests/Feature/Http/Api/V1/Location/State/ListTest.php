<?php

namespace Tests\Feature\Http\Api\V1\Location\State;

use App\Foundations\Enums\CacheKeyEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\Builders\Location\StateBuilder;
use Tests\TestCase;

class ListTest extends TestCase
{
    use DatabaseTransactions;

    protected StateBuilder $stateBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->stateBuilder = resolve(StateBuilder::class);

        Cache::tags(CacheKeyEnum::States->value)->flush();
    }

    /** @test */
    public function success_list()
    {
        $this->stateBuilder->create();
        $this->stateBuilder->create();
        $this->stateBuilder->create();

        $this->getJson(route('api.v1.location.state.list'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'status',
                        'short',
                        'country_code',
                        'country_name',
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_list_empty()
    {
        $this->getJson(route('api.v1.location.state.list'))
            ->assertJsonCount(0, 'data')
        ;
    }
    /** @test */
    public function success_search()
    {
        $this->stateBuilder->name('tttt')->short('tt')->create();
        $m_1 = $this->stateBuilder->name('aaaa')->short('aa')->create();
        $this->stateBuilder->name('wwww')->short('ww')->create();

        $this->getJson(route('api.v1.location.state.list', [
            'name' => 'aaa'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,]
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }
}
