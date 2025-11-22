<?php

namespace Tests\Feature\Api\Lists;

use App\Models\Lists\BonusType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BonusTypeListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_bonus_type_add_get_index(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->postJson(
            route('lists.bonus-types.store'),
            [
                'title' => 'test',
            ]
        )
            ->assertCreated();

        $this->getJson(route('lists.bonus-types.index'))
            ->assertOk()
            ->assertJsonCount(count(BonusType::getDefaultTypesList()) + 1, 'data');
    }

    public function test_bonus_type_get_list(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->postJson(
            route('lists.bonus-types.store'),
            [
                'title' => 'test',
            ]
        )
            ->assertCreated();

        $this->getJson(route('lists.bonus-types-list'))
            ->assertOk()
            ->assertJsonCount(count(BonusType::getDefaultTypesList()) + 1, 'data');
    }
}
