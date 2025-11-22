<?php

namespace Tests\Feature\Http\Api\OneC\Catalog\Certificates;

use App\Models\Catalog\Certificates\Certificate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CertificatesControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_list(): void
    {
        $this->loginAsModerator();

        Certificate::factory()
            ->create();

        $this->getJson(route('1c.certificates'))
            ->assertOk();
    }
}
