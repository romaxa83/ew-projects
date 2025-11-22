<?php

namespace Tests\Feature\Saas\GPS\Webhook;

use App\Models\Saas\GPS\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        Config::set('FLESPI_WEBHOOK_AUTH_TOKEN', 'test');

        $data = [
            "device_id" => 5179239,
            "new_data" => [
                "cid" => 1707776,
                "configuration" => [
                    "ident" => "350317174414478",
                    "phone" => "+380970438296",
                    "settings_polling" => "daily"
                ],
                "device_type_id" => 22,
                "id" => 5179239,
                "media_rotate" => 0,
                "media_ttl" => 31536000,
                "messages_rotate" => 0,
                "messages_ttl" => 2592000,
                "name" => "WEZOM01",
                "protocol_id" => 14
            ]
        ];

        /** @var $model Device */
        $model = $this->deviceBuilder->flespiDeviceId($data['device_id'])->create();

        $this->assertNotEquals($model->phone, phone_clear($data['new_data']['configuration']['phone']));
        $this->assertNotEquals($model->imei, $data['new_data']['configuration']['ident']);

        $this->putJson(route('v1.saas.flespi-webhook.update'), $data, [
            'Authorization' => 'test'
        ])
//            ->dump()
            ;

        $model->refresh();

        $this->assertEquals($model->phone, phone_clear($data['new_data']['configuration']['phone']));
        $this->assertEquals($model->imei, $data['new_data']['configuration']['ident']);
    }
}
