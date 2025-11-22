<?php

namespace Tests\Unit\Models\Users;

use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserHasCompanyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_has_company_relation(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create();

        $user->companyUser()->create(
            [
                'company_id' => $company->id,
            ]
        );

        self::assertEquals(
            $company->id,
            $user->company->id
        );
    }
}
