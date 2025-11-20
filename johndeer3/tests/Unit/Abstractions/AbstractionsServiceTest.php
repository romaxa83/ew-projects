<?php

namespace Tests\Unit\Abstractions;

use App\Models\Country;
use App\Models\JD\Client;
use App\Services\Catalog\CountryService;
use App\Services\JD\ClientService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AbstractionsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_toggle_active()
    {
        $model = Country::query()->first();

        $this->assertTrue($model->isActive());

        app(CountryService::class)->toggleActive($model);

        $model->refresh();

        $this->assertFalse($model->isActive());

        app(CountryService::class)->toggleActive($model);

        $model->refresh();

        $this->assertTrue($model->isActive());
    }

    /** @test */
    public function fail_not_include_trait()
    {
        $model = Client::query()->first();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Include Active Trait");

        app(ClientService::class)->toggleActive($model);
    }
}
