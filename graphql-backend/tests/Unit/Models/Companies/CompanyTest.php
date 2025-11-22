<?php

namespace Tests\Unit\Models\Companies;

use App\Models\Companies\Company;
use App\Models\Localization\Language;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_company_has_language()
    {
        $company = Company::factory()->create();

        self::assertInstanceOf(Language::class, $company->language);
    }
}
