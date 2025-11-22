<?php


namespace Tests\Feature\Saas\Translates;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TranslateIndexTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_index_translate_success()
    {
        $this->withoutExceptionHandling();
        $this->loginAsSaasSuperAdmin();

        $this->getJson(route('v1.saas.translates.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta',]);
    }
}
