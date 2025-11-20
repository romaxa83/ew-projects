<?php

namespace Tests\Feature\Api\Translations;

use App\Models\Version;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\TranslationBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class GetTranslationVersionTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $translationBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->translationBuilder = resolve(TranslationBuilder::class);
    }

    /** @test */
    public function success()
    {
        $this->translationBuilder->withVersion()->create();

        $version = Version::getVersionByAlias(Version::TRANSLATES)->version;
        $this->assertNotNull($version);

        $this->getJson(route('api.translate.version'))
            ->assertJson($this->structureSuccessResponse($version))
        ;
    }

    /** @test */
    public function fail_not_version()
    {
        $this->getJson(route('api.translate.version'))
            ->assertJson($this->structureSuccessResponse(null))
        ;
    }
}



