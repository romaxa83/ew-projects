<?php

namespace Tests\Feature\Http\Api\V1\Location\Timezone;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ListTest extends TestCase
{
    use DatabaseTransactions;

    protected $data;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_list()
    {
        $this->getJson(route('api.v1.location.timezone'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'timezone',
                        'title',
                        'country_code',
                        'country_name',
                    ]
                ]
            ])
            ->assertJsonCount(19, 'data')
        ;
    }
}
