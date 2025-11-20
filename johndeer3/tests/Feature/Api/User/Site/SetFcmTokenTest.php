<?php

namespace Tests\Feature\Api\User\Site;

use App\Services\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class SetFcmTokenTest extends TestCase
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
        $token = 'some_token';
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = [
            'fcm_token' => $token,
        ];

        $this->assertNull($user->fcm_token);

        $this->postJson(route('api.user.set-fcm-token'), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
            ]))
        ;

        $user->refresh();
        $this->assertEquals($user->fcm_token, $token);
    }

    /** @test */
    public function fail_without_token()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = [];

        $this->postJson(route('api.user.set-fcm-token'), $data)
            ->assertJson($this->structureErrorResponse(["The fcm token field is required."]))
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->mock(UserService::class, function(MockInterface $mock){
            $mock->shouldReceive("setFcmToken")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('api.user.set-fcm-token'), [
            'fcm_token' => 'token',
        ])
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $token = 'some_token';
        $this->userBuilder->create();

        $data = [
            'fcm_token' => $token,
        ];

        $this->postJson(route('api.user.set-fcm-token'), $data)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

