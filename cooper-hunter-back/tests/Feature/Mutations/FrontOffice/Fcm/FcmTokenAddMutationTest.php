<?php


namespace Tests\Feature\Mutations\FrontOffice\Fcm;


use App\GraphQL\Mutations\FrontOffice\Fcm\FcmTokenAddMutation;
use App\Models\Fcm\FcmToken;
use App\Models\Technicians\Technician;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class FcmTokenAddMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;
    use WithFaker;

    /**
     * Correct token for live testing
     */
    private const TOKEN = 'diAiePIHRLOLr4-lJcdg_4:APA91bESbsOzOFKPTGZIIX3-l5kr_9HH4vWjYls6UGGZ9aX9BoMAZwmzrt_Kmtmbd1eP2r3VY8Z4l2OOu2MPPticKAiYv3-n_HfPk3l0ellPMryWNENNRK80xO5R2Tmbq0tvqqqX6b42';

    private Technician $technician;

    public function setUp(): void
    {
        parent::setUp();

        $this->technician = $this->loginAsTechnicianWithRole();

        $this->mockAuthClass();
    }

    private function mockAuthClass(): void
    {
        $mock = $this->mock(
            ServiceAccountCredentials::class,
            fn(MockInterface $mock) => $mock
                ->shouldReceive('fetchAuthToken')
                ->once()
                ->andReturn(
                    [
                        'access_token' => $this->faker->lexify,
                        'expires_id' => 3600,
                        'token_type' => 'Bearer'
                    ]
                )
        )
            ->makePartial();

        $this->app->singleton(
            ServiceAccountCredentials::class,
            static fn(Container $app) => $mock
        );
    }

    public function test_success_add_token(): void
    {
        Http::fake(
            [
                sprintf(config('firebase.api_url'), config('firebase.default')) => Http::response(
                    status
                    :
                    Response::HTTP_OK
                )
            ]
        );

        $this->postGraphQL(
            GraphQLQuery::mutation(FcmTokenAddMutation::NAME)
                ->args(
                    [
                        'fcm_token' => self::TOKEN
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        FcmTokenAddMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseHas(
            FcmToken::class,
            [
                'member_type' => Technician::MORPH_NAME,
                'member_id' => $this->technician->id
            ]
        );
    }

    public function test_fail_add_token(): void
    {
        Http::fake(
            [
                sprintf(config('firebase.api_url'), config('firebase.default')) => Http::response(
                    status
                    :
                    Response::HTTP_BAD_REQUEST
                )
            ]
        );

        $this->postGraphQL(
            GraphQLQuery::mutation(FcmTokenAddMutation::NAME)
                ->args(
                    [
                        'fcm_token' => self::TOKEN
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.fcm_token_invalid')
                        ]
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            FcmToken::class,
            [
                'member_type' => Technician::MORPH_NAME,
                'member_id' => $this->technician->id
            ]
        );
    }
}
