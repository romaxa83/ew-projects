<?php

namespace Tests\Feature\Api\Library;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class GetLibraryDocumentListTest extends TestCase
{
    use DatabaseTransactions;

    public function testIfNotAuthorized()
    {
        $response = $this->getJson(route('library.index'));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testIfAuthorizedAllowed()
    {
        $this->loginAsCarrierDispatcher();

        $this->getJson(route('library.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta',]);
    }
}
