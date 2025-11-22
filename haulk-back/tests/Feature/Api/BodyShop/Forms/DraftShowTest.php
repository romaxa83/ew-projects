<?php

namespace Tests\Feature\Api\BodyShop\Forms;

use App\Models\Forms\Draft;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class DraftShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_has_no_access_for_unauthorized_users()
    {
        $this->getJson($this->route('somePath'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    private function route($params)
    {
        return route('body-shop.drafts.show', $params);
    }

    public function test_it_get_draft_for_all_users()
    {
        $this->loginAsBodyShopAdmin();

        $body = [
            'field1' => 'some text for field1',
            'field2' => 'some text for field2',
        ];

        $user = ['user_id' => $this->authenticatedUser->id];

        $path = 'somePath';
        Draft::factory()
            ->create(
                [
                    'path' => $path
                ] + [
                    'body' => $body
                ] + $user
            );

        $this->getJson($this->route($path))
            ->assertOk()
            ->assertJson(['data' => $body]);
    }

    public function test_it_get_not_exists_draft()
    {
        $this->loginAsBodyShopAdmin();
        $this->getJson($this->route('somePath'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
