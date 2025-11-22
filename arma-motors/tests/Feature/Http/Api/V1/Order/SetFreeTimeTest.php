<?php

namespace Tests\Feature\Http\Api\V1\Order;

use App\Exceptions\ErrorsCode;
use App\Models\AA\AAPost;
use App\Models\Dealership\Dealership;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class SetFreeTimeTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use OrderBuilder;
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
    public function success()
    {
        $dealership = Dealership::find(1);
        $data = $this->data();
        $data['data']['alias'] = $dealership->alias;

        $this->assertEmpty(AAPost::query()->get());

        $response = $this->post(
            route('api.v1.order.free-slot-time'),
            $data,
            $this->headers()
        )
            ->assertOk();

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $posts = AAPost::query()->get();

        $this->assertNotEmpty($posts);

        $post = $posts[0];

        $this->assertEquals($post->name, $data['data']['name']);
        $this->assertEquals($post->uuid, $data['data']['id']);
        $this->assertEquals($post->dealership->alias, $data['data']['alias']);

        $this->assertNotEmpty($post->schedules);
        $this->assertCount(count($data['data']['schedule']) , $post->schedules);
    }

    /** @test */
    public function fail_wrong_dealership_alias()
    {
        $data = $this->data();
        $data['data']['alias'] = 'wrong alias';

        $response = $this->post(
            route('api.v1.order.free-slot-time'),
            $data,
            $this->headers() + ['Content-Language' => 'en']
        )
            ->assertStatus(ErrorsCode::BAD_REQUEST);

        $this->assertFalse($response->json('success'));
        $this->assertEquals($response->json('data'), "The selected data.alias is invalid.");
    }

    /** @test */
    public function wrong_auth_token()
    {
        $dealership = Dealership::find(1);
        $data = $this->data();
        $data['data']['alias'] = $dealership->alias;

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $response = $this->post(
            route('api.v1.order.free-slot-time'),
            $data,
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH);

        $this->assertEquals($response->json('data'), 'Bad authorization token');
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function without_auth_token()
    {
        $dealership = Dealership::find(1);
        $data = $this->data();
        $data['data']['alias'] = $dealership->alias;

        $response = $this->post(
            route('api.v1.order.free-slot-time'),
            $data,
            []
        )
            ->assertStatus(ErrorsCode::NOT_AUTH);

        $this->assertEquals($response->json('data'), 'Missing authorization header');
        $this->assertFalse($response->json('success'));
    }

    public function data(): array
    {
        return [
            'data' => [
                'id' => '3c13fafb-79d6-11ec-8277-4cd98fc26f14',
                'name' => 'Виготовка П №1',
                'alias' => 'arma-motors-renault',
                'schedule' => [
                    [
                        'date' => '2022-01-21T00:00:00',
                        'startDate' => '2022-01-21T08:00:00',
                        'endDate' => '2022-01-21T20:00:00',
                        'workingDay' => true,
                    ],
                    [
                        'date' => '2022-01-22T00:00:00',
                        'startDate' => '2022-01-22T08:00:00',
                        'endDate' => '2022-01-22T20:00:00',
                        'workingDay' => true,
                    ],
                    [
                        'date' => '2022-01-23T00:00:00',
                        'startDate' => '2022-01-23T08:00:00',
                        'endDate' => '2022-01-23T20:00:00',
                        'workingDay' => true,
                    ]
                ]
            ]
        ];
    }
}



