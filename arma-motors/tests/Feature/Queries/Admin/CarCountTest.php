<?php

namespace Tests\Feature\Queries\Admin;

use App\Exceptions\ErrorsCode;
use App\Models\User\Car;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class CarCountTest extends TestCase
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
        $user = User::factory()->new(['status' => 1])->create();

        Car::factory()->new(['inner_status' => 0, 'user_id' => $user->id])->count(2)->create();  // draft
        Car::factory()->new(['inner_status' => 1, 'user_id' => $user->id])->count(3)->create();  // active
        Car::factory()->new(['inner_status' => 2, 'user_id' => $user->id])->count(4)->create();  // verify

        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr($this->user_car_status_draft));

        $responseData = $response->json('data.carCount');

        $this->assertArrayHasKey('key', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals('count', $responseData['key']);
        $this->assertEquals(2, $responseData['name']);
    }

    /** @test */
    public function success_get_moderate()
    {
        $user = User::factory()->new(['status' => 1])->create();

        Car::factory()->new(['inner_status' => 0, 'user_id' => $user->id])->count(2)->create();  // draft
        Car::factory()->new(['inner_status' => 1, 'user_id' => $user->id])->count(3)->create();  // active
        Car::factory()->new(['inner_status' => 2, 'user_id' => $user->id])->count(4)->create();  // verify

        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr($this->user_car_status_moderate));

        $responseData = $response->json('data.carCount');

        $this->assertArrayHasKey('key', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals('count', $responseData['key']);
        $this->assertEquals(3, $responseData['name']);
    }

    /** @test */
    public function success_get_verify()
    {
        $user = User::factory()->new(['status' => 1])->create();

        Car::factory()->new(['inner_status' => 0, 'user_id' => $user->id])->count(2)->create();  // draft
        Car::factory()->new(['inner_status' => 1, 'user_id' => $user->id])->count(3)->create();  // active
        Car::factory()->new(['inner_status' => 2, 'user_id' => $user->id])->count(4)->create();  // verify

        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr($this->user_car_status_verify));

        $responseData = $response->json('data.carCount');

        $this->assertArrayHasKey('key', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals('count', $responseData['key']);
        $this->assertEquals(4, $responseData['name']);
    }

    /** @test */
    public function success_all()
    {
        $user = User::factory()->new(['status' => 1])->create();

        Car::factory()->new(['inner_status' => 0, 'user_id' => $user->id])->count(2)->create();  // draft
        Car::factory()->new(['inner_status' => 1, 'user_id' => $user->id])->count(3)->create();  // active
        Car::factory()->new(['inner_status' => 2, 'user_id' => $user->id])->count(4)->create();  // verify

        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrWithoutStatus());

        $responseData = $response->json('data.carCount');

        $this->assertArrayHasKey('key', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals('count', $responseData['key']);
        $this->assertEquals(9, $responseData['name']);
    }

    /** @test */
    public function not_auth()
    {
        $user = User::factory()->new(['status' => 1])->create();

        Car::factory()->new(['inner_status' => 0, 'user_id' => $user->id])->count(2)->create();  // draft
        Car::factory()->new(['inner_status' => 1, 'user_id' => $user->id])->count(3)->create();  // active
        Car::factory()->new(['inner_status' => 2, 'user_id' => $user->id])->count(4)->create();  // verify

        $this->adminBuilder()->create();

        $response = $this->graphQL($this->getQueryStr($this->user_car_status_draft));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr($status): string
    {
        return  sprintf('{
            carCount (status: %s){
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
            carCount{
                key
                name
               }
            }'
        );
    }
}

