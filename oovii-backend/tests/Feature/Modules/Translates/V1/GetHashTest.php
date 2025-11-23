<?php

namespace Tests\Feature\Modules\Translates\V1;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Translates\Repositories\TranslateRepository;

class GetHashTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    private TranslateRepository $repo;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->repo = app(TranslateRepository::class);
    }

    /** @test */
    public function success(): void
    {
        $resHash = $this->getJson(route('api.v1.mobile.translates.hash'))
            ->assertOk()
            ->assertJsonStructure($this->structureResponse());

        $data = SetTranslatesTest::data();
        // записуем перевод, в ответ получаем хеш
        $res = $this->postJson(route('api.v1.mobile.translates.set'), $data);

        $this->assertNotEquals($resHash->json("data"), $res->json('data'));

        $resHashUpdate = $this->getJson(route('api.v1.mobile.translates.hash'))
            ->assertOk()
            ->assertJsonStructure($this->structureResponse());

        $this->assertEquals($resHashUpdate->json("data"), $res->json('data'));
    }
}


