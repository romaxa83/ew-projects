<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Companies;

use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CompanyGetNameTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_id_from_name_when_name_and_owner_is_null(): void
    {
        $company = new Company();

        $id = 1;
        $company->id = $id;
        self::assertEquals($id, $company->getName());

        $user = new User();
        $firstName = 'First name';
        $middleName = 'Middle name';
        $lastName = 'Last name';

        $user->first_name = $firstName;
        $user->middle_name = $middleName;
        $user->last_name = $lastName;

        $company->setRelation('owner', $user);
        self::assertEquals(
            $user->getName(),
            $company->getName()
        );

        $name = 'Name';
        $company->name = $name;
        self::assertEquals($name, $company->getName());
    }
}
