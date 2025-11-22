<?php


namespace Tests\Feature\Saas\Translates;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TranslateDeleteTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_delete_translate_success()
    {
        $this->markTestSkipped();
    }
}
