<?php

namespace Tests\Feature\Modules\Providers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class RegistrationTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    /*public function test_it_returns_region_and_city_validation_errors(): void
    {
        $providerData = [
            'name' => 'new Provider',
            'email' => 'provider@gmail.com',
            'phone' => '+380501234567',
            'region_code' => 123456,
            'city_code' => 123456,
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'company' => '12345678'
        ];

        $this->post(
            route('admin.register'),
            $providerData
        )
            ->assertInvalid(['region_code', 'city_code']);

        $providerData['region_code'] = 299;
        $providerData['city_code'] = 837490;

        $this->post(
            route('admin.register'),
            $providerData
        )
            ->assertValid(['region_code', 'city_code']);
    }*/
}

