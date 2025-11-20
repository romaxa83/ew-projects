<?php

namespace Tests\Feature\Api\Common;

use App\Models\Languages;
use App\Repositories\LanguageRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class GetLanguageTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success()
    {
        $count = Languages::count();

        $this->getJson(route('api.language'))
            ->assertJson($this->structureSuccessResponse([
                "en" => "English"
            ]))
            ->assertJsonCount($count, 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $this->mock(LanguageRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getForSelect")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.language'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }
}

