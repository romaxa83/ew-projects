<?php


namespace Tests\Feature\Saas\Translates;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TranslateListTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_list_translate_success()
    {
        $this->loginAsSaasSuperAdmin();

        $this->getJson(route('v1.saas.translates.index'))
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
}
