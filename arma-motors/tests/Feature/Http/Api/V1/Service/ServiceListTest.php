<?php

namespace Tests\Feature\Http\Api\V1\Service;

use App\Exceptions\ErrorsCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class ServiceListTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->armaAuth();
    }

    public function headers()
    {
        return [
            'Authorization' => 'Basic d2V6b20tYXBpOndlem9tLWFwaQ=='
        ];
    }

    /** @test */
    public function get_list()
    {
        $response = $this->get(
            route('api.v1.services'),
            $this->headers()
        )
            ->assertOk()
            ->assertJsonStructure(['data' => [ 0 => [
                'id',
                'alias',
                'name'
            ]]])
        ;
        $this->assertNotEmpty($response->json('data'));
    }
    /** @test */
    public function wrong_auth_token()
    {
        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $response = $this->get(
            route('api.v1.services'),
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH)
        ;

        $this->assertEquals($response->json('data'), 'Bad authorization token');
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function without_auth_token()
    {
        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $response = $this->get(
            route('api.v1.services'),
            []
        )
            ->assertStatus(ErrorsCode::NOT_AUTH)
        ;

        $this->assertEquals($response->json('data'), 'Missing authorization header');
        $this->assertFalse($response->json('success'));
    }
}


