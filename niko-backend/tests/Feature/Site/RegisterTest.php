<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function register_success()
    {
        $data = [
            'first_name' => 'test',
            'last_name' => 'test',
            'phone' => '+38(095)4512222',
            'email' => 'test@test.com'
        ];

        $this->assertTrue(true);
//        dd($data);
//http://192.168.180.1/api/v1/registration'
        $response = $this->post(route('api.registration'), $data)
            ->dump()
        ;
    }
}
