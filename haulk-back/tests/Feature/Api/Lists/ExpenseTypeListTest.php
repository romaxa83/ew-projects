<?php

namespace Tests\Feature\Api\Lists;

use App\Models\Lists\ExpenseType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExpenseTypeListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_expense_type_add_index(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->postJson(
            route('lists.expense-types.store'),
            [
                'title' => 'test',
            ]
        )
            ->assertCreated();

        $this->getJson(route('lists.expense-types.index'))
            ->assertOk()
            ->assertJsonCount(count(ExpenseType::getDefaultTypesList()) + 1, 'data');
    }

    public function test_expense_type_get_list(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->postJson(
            route('lists.expense-types.store'),
            [
                'title' => 'test',
            ]
        )
            ->assertCreated();

        $this->getJson(route('lists.expense-types-list'))
            ->assertOk()
            ->assertJsonCount(count(ExpenseType::getDefaultTypesList()) + 1, 'data');
    }
}
