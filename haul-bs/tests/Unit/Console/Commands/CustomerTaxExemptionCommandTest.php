<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\Customers\CustomerTaxExemptionCommand;
use App\Enums\Customers\CustomerTaxExemptionStatus;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerTaxExemption;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomerTaxExemptionCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_change_status(): void
    {
        $user = Customer::factory()
            ->has(CustomerTaxExemption::factory([
                'status' => CustomerTaxExemptionStatus::ACCEPTED,
                'date_active_to' => now(),
            ]),'taxExemption')
            ->create();
        $tax = $user->taxExemption;

        $this->artisan(CustomerTaxExemptionCommand::class)
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas(CustomerTaxExemption::TABLE, [
            'id' => $tax->id,
            'status' => CustomerTaxExemptionStatus::EXPIRED,
        ]);
    }
}
