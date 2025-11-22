<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Companies;

use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CompanyToOwnerRelationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_get_user_by_relation(): void
    {
        $company = Company::factory()->create();

        User::factory()
            ->withCompany($company)
            ->count(10)
            ->create();

        $user = User::factory()
            ->withCompany($company, true)
            ->create();

        self::assertEquals($user->id, $company->owner->id);
    }
}
