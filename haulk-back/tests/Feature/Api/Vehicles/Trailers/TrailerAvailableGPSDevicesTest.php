<?php

namespace Api\Vehicles\Trailers;

use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TrailerAvailableGPSDevicesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_list(): void
    {
        $user = $this->loginAsCarrierAdmin();
        $company = $user->getCompany();
        $company->save();

        $company2 = Company::factory()->create();

        $device1 = Device::factory(['company_id' => $company->id])->create();
        $device2 = Device::factory(['company_id' => $company->id])->create();
        $device3 = Device::factory(['company_id' => $company->id])->create();
        $device4 = Device::factory(['company_id' => $company->id])->create();
        $device5 = Device::factory(['company_id' => $company->id])->create();
        $device6 = Device::factory(['company_id' => $company->id])->create();
        $device7 = Device::factory(['company_id' => $company2->id])->create();

        factory(Truck::class)->create(['gps_device_id' => $device1->id]);
        factory(Trailer::class)->create(['gps_device_id' => $device2->id]);
        factory(Trailer::class)->create(['gps_device_id' => $device3->id]);

        $response = $this->getJson(route('trailers.available-gps-devices'))
            ->assertOk();

        $this->assertCount(3, $response['data']);
    }

    public function test_it_list_with_current_trailer_id(): void
    {
        $user = $this->loginAsCarrierAdmin();
        $company = $user->getCompany();
        $company->save();

        $company2 = Company::factory()->create();

        $device1 = Device::factory(['company_id' => $company->id])->create();
        $device2 = Device::factory(['company_id' => $company->id])->create();
        $device3 = Device::factory(['company_id' => $company->id])->create();
        $device4 = Device::factory(['company_id' => $company->id])->create();
        $device5 = Device::factory(['company_id' => $company->id])->create();
        $device6 = Device::factory(['company_id' => $company->id])->create();
        $device7 = Device::factory(['company_id' => $company2->id])->create();

        factory(Truck::class)->create(['gps_device_id' => $device1->id]);
        factory(Trailer::class)->create(['gps_device_id' => $device2->id]);
        $currentTrailer = factory(Trailer::class)->create(['gps_device_id' => $device3->id]);

        $response = $this->getJson(route('trailers.available-gps-devices', ['trailer_id' => $currentTrailer->id]))
            ->assertOk();

        $this->assertCount(4, $response['data']);
    }

    public function test_it_forbidden_for_company_without_gps(): void
    {
        $this->loginAsCarrierAdmin();
        $this->getJson(route('trailers.available-gps-devices'))
            ->assertForbidden();
    }
}
