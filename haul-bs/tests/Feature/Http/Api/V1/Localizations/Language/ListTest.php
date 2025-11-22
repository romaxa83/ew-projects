<?php

namespace Tests\Feature\Http\Api\V1\Localizations\Language;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ListTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_list()
    {
        $this->getJson(route('api.v1.localization.languages'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'name',
                        'native',
                        'slug',
                    ]
                ]
            ])
            ->assertJsonCount(4, 'data')
        ;
    }
}

