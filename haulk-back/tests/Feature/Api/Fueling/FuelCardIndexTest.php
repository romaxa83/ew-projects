<?php

namespace Feature\Api\Fueling;

use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Orders\Order;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class FuelCardIndexTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('fuel-cards.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierDriver();

        $this->getJson(route('fuel-cards.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_for_super_admin(): void
    {
        $this->loginAsCarrierSuperAdmin();
        FuelCard::factory()->count(15)->create();
        $response = $this->getJson(route('fuel-cards.index'))
            ->assertOk();

        $comments = $response['data'];
        $this->assertCount(15, $comments);
    }

    public function test_it_show_all_for_admin(): void
    {
        $this->loginAsCarrierAdmin();

        $this->getJson(route('fuel-cards.index'))
            ->assertOk();
    }

    public function test_it_show_all_for_accountant(): void
    {
        $this->loginAsCarrierAccountant();

        $this->getJson(route('fuel-cards.index'))
            ->assertOk();
    }
}
