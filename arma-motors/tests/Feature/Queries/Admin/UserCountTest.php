<?php

namespace Tests\Feature\Queries\Admin;

use App\Exceptions\ErrorsCode;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class UserCountTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_get_draft()
    {
        User::factory()->new(['status' => 0])->count(2)->create();  // draft
        User::factory()->new(['status' => 1])->count(3)->create();  // active
        User::factory()->new(['status' => 2])->count(4)->create();  // verify

        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr($this->user_status_draft));

        $responseData = $response->json('data.userCount');

        $this->assertArrayHasKey('key', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals('count', $responseData['key']);
        $this->assertEquals(2, $responseData['name']);
    }

    /** @test */
    public function success_get_active()
    {
        User::factory()->new(['status' => 0])->count(2)->create();  // draft
        User::factory()->new(['status' => 1])->count(3)->create();  // active
        User::factory()->new(['status' => 2])->count(4)->create();  // verify

        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr($this->user_status_active));

        $responseData = $response->json('data.userCount');

        $this->assertArrayHasKey('key', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals('count', $responseData['key']);
        $this->assertEquals(3, $responseData['name']);
    }

    /** @test */
    public function success_get_verify()
    {
        User::factory()->new(['status' => 0])->count(2)->create();  // draft
        User::factory()->new(['status' => 1])->count(3)->create();  // active
        User::factory()->new(['status' => 2])->count(4)->create();  // verify

        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr($this->user_status_verify));

        $responseData = $response->json('data.userCount');

        $this->assertArrayHasKey('key', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals('count', $responseData['key']);
        $this->assertEquals(4, $responseData['name']);
    }

    /** @test */
    public function success_all()
    {
        User::factory()->new(['status' => 0])->count(2)->create();  // draft
        User::factory()->new(['status' => 1])->count(3)->create();  // active
        User::factory()->new(['status' => 2])->count(4)->create();  // verify

        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrWithoutStatus());

        $responseData = $response->json('data.userCount');

        $this->assertArrayHasKey('key', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals('count', $responseData['key']);
        $this->assertEquals(9, $responseData['name']);
    }

    /** @test */
    public function not_auth()
    {
        User::factory()->new(['status' => 0])->count(2)->create();  // draft
        User::factory()->new(['status' => 1])->count(3)->create();  // active
        User::factory()->new(['status' => 2])->count(4)->create();  // verify

        $this->adminBuilder()->create();

        $response = $this->graphQL($this->getQueryStr($this->user_status_draft));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr($status): string
    {
        return  sprintf('{
            userCount (status: %s){
                key
                name
               }
            }',
            $status
        );
    }

    public static function getQueryStrWithoutStatus(): string
    {
        return  sprintf('{
            userCount{
                key
                name
               }
            }'
        );
    }
}
