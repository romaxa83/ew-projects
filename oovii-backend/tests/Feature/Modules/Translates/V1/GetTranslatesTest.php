<?php

namespace Tests\Feature\Modules\Translates\V1;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Translates\Repositories\TranslateRepository;

class GetTranslatesTest extends TestCase
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
        $data = SetTranslatesTest::data();
        // записуем перевод
        $this->postJson(route('api.v1.mobile.translates.set'), $data);

        $this->getJson(route('api.v1.mobile.translates.get'))
            ->assertOk()
            ->assertJson($this->structureSucessResponse([
                "button" => $data["button"],
                "text" => $data["text"],
            ]));
    }

    /** @test */
    public function success_without_some_lang_translates(): void
    {
        $data = SetTranslatesTest::data();
        unset(
            $data["text"]["kk"]
        );
        // записуем перевод
        $this->postJson(route('api.v1.mobile.translates.set'), $data);

        $this->getJson(route('api.v1.mobile.translates.get'))
            ->assertOk()
            ->assertJson($this->structureSucessResponse([
                "button" => [
                    "ru" => $data["button"]["ru"],
                    "kk" => $data["button"]["kk"],
                ],
                "text" => [
                    "ru" => $data["text"]["ru"],
                    "kk" => null,
                ],
            ]));
    }
}


