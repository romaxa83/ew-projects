<?php

namespace Tests\Unit\Service\Catalog;

use App\Models\Country;
use App\Services\Catalog\CountryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CountryServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_create_country(): void
    {
        $data = [
            'name' => 'test',
            'active' => true,
        ];
        $service = app(CountryService::class);

        $this->assertNull(Country::query()->where('name', $data['name'])->first());

        $result = $service->create($data);

        $this->assertNotNull($model = Country::query()->where('name', $data['name'])->first());

        $this->assertEquals($result->id, $model->id);
    }
}

