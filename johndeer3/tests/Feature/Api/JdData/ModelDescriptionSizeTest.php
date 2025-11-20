<?php

namespace Tests\Feature\Api\JdData;

use App\Models\User\User;
use App\Repositories\JD\ProductRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class ModelDescriptionSizeTest extends TestCase
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
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $repo = app(ProductRepository::class);

        $this->getJson(route('api.model-descriptions.sizes'))
            ->assertJson(
                $this->structureSuccessResponse($repo->getSizeForSelect())
            )
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->mock(ProductRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getSizeForSelect")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.model-descriptions.sizes'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.model-descriptions.sizes'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}



