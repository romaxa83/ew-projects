<?php

namespace Tests\Feature\Http\Api\V1\Location\City;

use App\Foundations\Http\Requests\Common\SearchRequest;
use App\Foundations\Modules\Location\Models\City;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Location\CityBuilder;
use Tests\Builders\Location\StateBuilder;
use Tests\TestCase;

class ListTest extends TestCase
{
    use DatabaseTransactions;

    protected StateBuilder $stateBuilder;
    protected CityBuilder $cityBuilder;

    protected $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->stateBuilder = resolve(StateBuilder::class);
        $this->cityBuilder = resolve(CityBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->cityBuilder->create();
        $this->cityBuilder->create();
        $this->cityBuilder->create();

        $this->getJson(route('api.v1.location.city-autocomplete'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'zip',
                        'state_id',
                        'state_name',
                        'state_short_name',
                        'timezone',
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
        $this->getJson(route('api.v1.location.city-autocomplete'))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_list_default_limit()
    {
        City::factory()->count(30)->create();

        $this->getJson(route('api.v1.location.city-autocomplete'))
            ->assertJsonCount(SearchRequest::DEFAULT_LIMIT, 'data')
        ;
    }

    /** @test */
    public function success_list_limit()
    {
        City::factory()->count(30)->create();

        $this->getJson(route('api.v1.location.city-autocomplete', [
            'limit' => 5
        ]))
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_search()
    {
        $model = $this->cityBuilder->name('aaaa')->create();
        $this->cityBuilder->name('bbb')->create();
        $this->cityBuilder->name('tttt')->create();
        $this->cityBuilder->name('zzzz')->create();

        $this->getJson(route('api.v1.location.city-autocomplete', [
            'search' => 'aaa'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $model->id,]
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_filter_by_zip()
    {
        $model = $this->cityBuilder->zip('10097')->create();
        $this->cityBuilder->zip('80096')->create();
        $this->cityBuilder->zip('80097')->create();

        $this->getJson(route('api.v1.location.city-autocomplete', [
            'zip' => '8009'
        ]))
            ->assertJsonCount(2, 'data')
        ;
    }
}
