<?php

namespace Tests\Feature\Api\GPS;

use App\Models\GPS\Message;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DataReceivingTest extends TestCase
{
    use DatabaseTransactions;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    public function test_it_update_by_super_admin()
    {
        $data = [
            [
                'ident' => '352625333222111',
                'position.direction' => 273.61,
                'position.latitude' => 49.069782,
                'position.longitude' => 28.632826,
                'timestamp' => 1650636570.426424,
                'vehicle.mileage' => 103.5,
                'position.speed' => 50.3,
                'battery.level' => 30,
                'battery.charging.status' => true,
                'driving.status' => true,
                'idle.status' => false,
                'engine.status' => true,
            ]
        ];

        $this->assertEmpty(Message::all());

        $this->postJson(route('gps.receive-data'),
            $data , [
              'Authorization' => config('flespi.webhook.auth_token')
            ])
            ->assertOk();

        $model = Message::first();

        $this->assertEquals($model->imei, $data[0]['ident']);
        $this->assertEquals($model->data, $data[0]);
    }
}
