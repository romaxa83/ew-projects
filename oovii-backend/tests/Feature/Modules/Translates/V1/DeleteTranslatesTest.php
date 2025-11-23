<?php

namespace Tests\Feature\Modules\Translates\V1;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Translates\Repositories\TranslateRepository;

class DeleteTranslatesTest extends TestCase
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
    public function success_remove_all(): void
    {
        $data = SetTranslatesTest::data();
        // записуем перевод
        $this->postJson(route('api.v1.mobile.translates.set'), $data);

        $this->assertNotEmpty($this->repo->getTranslates());

        $this->deleteJson(route('api.v1.mobile.translates.delete'))
            ->assertOk()
            ->assertJsonStructure($this->structureResponse());

        $this->assertEmpty($this->repo->getTranslates());
    }

    /** @test */
    public function success_remove_all_if_empty(): void
    {
        $this->assertEmpty($this->repo->getTranslates());

        $this->deleteJson(route('api.v1.mobile.translates.delete'))
            ->assertOk()
            ->assertJsonStructure($this->structureResponse());

        $this->assertEmpty($this->repo->getTranslates());
    }

    /** @test */
    public function success_remove_by_alias(): void
    {
        $data = SetTranslatesTest::data();
        // записуем перевод
        $this->postJson(route('api.v1.mobile.translates.set'), $data);

        $alias = "button";
        $this->assertNotEmpty($this->repo->getTranslateByKey($alias));

        $this->deleteJson(route('api.v1.mobile.translates.delete', ["alias" => $alias]))
            ->assertOk()
            ->assertJsonStructure($this->structureResponse());

        $this->assertEmpty($this->repo->getTranslateByKey($alias));
        $this->assertNotEmpty($this->repo->getTranslates());
    }

    /** @test */
    public function success_remove_by_alias_if_not(): void
    {
        $data = SetTranslatesTest::data();
        // записуем перевод
        $this->postJson(route('api.v1.mobile.translates.set'), $data);

        $alias = "not";

        $this->deleteJson(route('api.v1.mobile.translates.delete', ["alias" => $alias]))
            ->assertOk()
            ->assertJsonStructure($this->structureResponse());
    }
}



