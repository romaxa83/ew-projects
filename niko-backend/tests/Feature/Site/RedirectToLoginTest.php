<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectToLoginTest extends TestCase
{
    /** @test */
    public function redirect_to_login_from_main()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertLocation('wezom/login');
    }
}
