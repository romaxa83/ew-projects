<?php

namespace Tests\Feature\Modules\Translates\V1;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Core\Traits\Hasher;
use WezomCms\Translates\Repositories\TranslateRepository;

class SetTranslatesTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;
    use Hasher;

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
        $data = self::data();

        $countRow = count($data, COUNT_RECURSIVE) - count($data);

        $translates = $this->repo->getTranslates();

        $this->assertEmpty($translates);

        $res = $this->postJson(
            route('api.v1.mobile.translates.set'),
            $data
        )
            ->assertOk()
            ->assertJsonStructure($this->structureResponse());

        $translates = $this->repo->getTranslates();

        $this->assertNotEmpty($translates);
        $this->assertEquals($this->hash($translates), $res->json('data'));

        $this->assertCount($countRow, $translates);

        foreach ($translates as $translate){
            $this->assertEquals($translate->text, $data[$translate->key][$translate->locale]);
        }
    }

    /** @test */
    public function success_only_expect_language(): void
    {
        $data = self::data();
        $countExpectRow = count($data, COUNT_RECURSIVE) - count($data);

        $data["button"]["es"] = "button_es";
        $data["text"]["es"] = "text_es";

        $countRow = count($data, COUNT_RECURSIVE) - count($data);

        $this->assertNotEquals($countExpectRow, $countRow);

        $this->postJson(
            route('api.v1.mobile.translates.set'),
            $data
        );

        $translates = $this->repo->getTranslates();

        $this->assertCount($countExpectRow, $translates);
    }

    /** @test */
    public function success_update(): void
    {
        $data = self::data();

        $res = $this->postJson(
            route('api.v1.mobile.translates.set'),
            $data
        );

        $data["button"]["ru"] = "update_button_ru";
        $data["button"]["kk"] = "update_button_kk";
        $data["button"]["en"] = "update_button_en";
        $data["create"]["ru"] = "create_ru";
        $data["create"]["en"] = "create_en";
        $data["create"]["kk"] = "create_kk";

        $translates = $this->repo->getTranslates();
        foreach ($translates as $translate){
            if($translate->key === "button" && $translate->locale === "en" ){
                $this->assertNotEquals($translate->text, $data["button"]["en"]);
            }
            if($translate->key === "button" && $translate->locale === "kk" ){
                $this->assertNotEquals($translate->text, $data["button"]["kk"]);
            }
            if($translate->key === "button" && $translate->locale === "ru" ){
                $this->assertNotEquals($translate->text, $data["button"]["ru"]);
            }
        }

        $resNew = $this->postJson(
            route('api.v1.mobile.translates.set'),
            $data
        );

        $translates = $this->repo->getTranslates();
        foreach ($translates as $translate){
            if($translate->key === "button" && $translate->locale === "en" ){
                $this->assertEquals($translate->text, $data["button"]["en"]);
            }
            if($translate->key === "button" && $translate->locale === "kk" ){
                $this->assertEquals($translate->text, $data["button"]["kk"]);
            }
            if($translate->key === "button" && $translate->locale === "ru" ){
                $this->assertEquals($translate->text, $data["button"]["ru"]);
            }
        }

        $this->assertNotEquals($res->json("data"), $resNew->json("data"));
    }

    public static function data(): array
    {
        return [
            "button" => [
                "ru" => "button_ru",
                "kk" => "button_kk",
            ],
            "text" => [
                "ru" => "text_ru",
                "kk" => "text_kk",
            ],
        ];
    }
}


