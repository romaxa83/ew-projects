<?php

namespace Tests\Feature\CKFinder;

use App\GraphQL\Mutations\BackOffice\Admins\AdminLoginMutation;
use App\Models\Admins\Admin;
use App\Providers\CKFinderServiceProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CKFinderAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public const LOGIN_MUTATION = AdminLoginMutation::NAME;
    public const LOGIN_RESPONSE_JSON_STRUCTURE = [
        'data' =>
            [
                self::LOGIN_MUTATION =>
                    [
                        'access_token',
                    ]
            ]
    ];

    public function test_authenticated_admin_can_use_ckfinder_connector(): void
    {
        $admin = Admin::factory()->create();

        $params = sprintf(
            'username: "%s", password: "%s"',
            $admin->email,
            'password',
        );

        $query = sprintf(
            'mutation { %s (
                        %s
                    ) { access_token } }',
            self::LOGIN_MUTATION,
            $params,
        );

        $response = $this->postGraphQLBackOffice(compact('query'));
        $response->assertJsonCount(1, 'data');
        $response->assertJsonStructure(static::LOGIN_RESPONSE_JSON_STRUCTURE);

        $accessToken = $response->json('data.' . self::LOGIN_MUTATION . '.access_token');

        static::assertTrue(app()->providerIsLoaded(CKFinderServiceProvider::class));

        $this->getJson(route('ckfinder_connector', ['access_token' => $accessToken, 'command' => 'Init']))
            ->assertOk();

        /**
         * Deceive phpunit as it checks buffer before start and end of the test
         * issue with buffer clearing by ckfinder_connector itself
         *
         * @see CKFinder::afterCommand() Clear any garbage from the output
         */
        ob_start();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }
}
