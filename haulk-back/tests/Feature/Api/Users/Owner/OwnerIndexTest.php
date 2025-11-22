<?php

namespace Api\Users\Owner;

use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OwnerIndexTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_related_fields(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $owner = $this->ownerFactory(['first_name' => 'TestOwner']);

        $response = $this->getJson(route('users.index', ['name' => 'TestOwner']))
            ->assertOk();

        $this->assertFalse($response['data'][0]['hasRelatedOwnerTrucks']);
        $this->assertFalse($response['data'][0]['hasRelatedOwnerTrailers']);

        factory(Truck::class)->create(['owner_id' => $owner->id]);

        $response = $this->getJson(route('users.index', ['name' => 'TestOwner']))
            ->assertOk();

        $this->assertTrue($response['data'][0]['hasRelatedOwnerTrucks']);
        $this->assertFalse($response['data'][0]['hasRelatedOwnerTrailers']);

    }
}
