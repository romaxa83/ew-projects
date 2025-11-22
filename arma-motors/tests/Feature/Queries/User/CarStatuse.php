<?php

namespace Tests\Feature\Queries\User;

use App\Models\User\Car;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class CarStatuse extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $responseData = $response->json('data.carStatuses');

        $this->assertCount(2, $responseData);

        $this->assertEquals(Car::DRAFT, $responseData[0]['key']);
        $this->assertEquals(__('translation.car.status.draft'), $responseData[0]['name']);

        $this->assertEquals(Car::MODERATE, $responseData[1]['key']);
        $this->assertEquals(__('translation.car.status.moderate'), $responseData[1]['name']);
    }

    public static function getQueryStr(): string
    {
        return "{carStatuses {key, name}}";
    }
}

