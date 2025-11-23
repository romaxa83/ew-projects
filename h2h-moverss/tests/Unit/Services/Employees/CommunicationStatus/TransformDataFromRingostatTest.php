<?php

namespace Tests\Unit\Services\Employees\CommunicationStatus;

use App\Services\Employees\CommunicationStatusService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TransformDataFromRingostatTest extends TestCase
{
    use DatabaseTransactions;

    protected array $data = [];

    protected CommunicationStatusService $service;
    public function setUp(): void
    {
        $this->service = resolve(CommunicationStatusService::class);

        $this->data = [
            348104 => [
                "id" => 476244,
                "staffId" => 348104,
                "status" => false,
                "fio" => "Wezom Test",
                "email" => "rodomanov.r@wezom.com",
                "extensionNumber" => "666",
                "departments" => [
                    0 => 11309
                ],
                "directions" => [
                    "main" => [],
                    "additional" => [
                        [
                            "id" => 864049,
                            "type" => "sip",
                            "direction" => "h2hmoverscom_wezom_test",
                            "gateway" => "sip.ringostat.net",
                            "enabledForAwd" => true
                        ]
                    ]
                ]
            ],
            348105 => [
                "id" => 476244,
                "staffId" => 348105,
                "status" => true,
                "fio" => "Wezom Test",
                "email" => "rodomanov_2.r@wezom.com",
                "extensionNumber" => "667",
                "departments" => [
                    0 => 11309
                ],
                "directions" => [
                    "main" => [
                        [
                            "id" => 864049,
                            "type" => "sip",
                            "direction" => "h2hmoverscom_wezom_test_3",
                            "gateway" => "sip.ringostat.net",
                            "enabledForAwd" => true
                        ]
                    ],
                    "additional" => [

                    ]
                ]
            ],

        ];
    }

    /** @test */
    public function success_transform()
    {
        $result =  $this->service->transformDataFromRingostat($this->data);

        $this->assertCount(2, $result);

        $this->assertEquals($result[0]['id'], current($this->data)['staffId']);
        $this->assertEquals($result[0]['status'], current($this->data)['status']);

        $this->assertEquals($result[1]['id'], last($this->data)['staffId']);
        $this->assertEquals($result[1]['status'], last($this->data)['status']);
    }
}


