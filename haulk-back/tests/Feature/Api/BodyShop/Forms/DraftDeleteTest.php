<?php

namespace Tests\Feature\Api\BodyShop\Forms;

use App\Models\Forms\Draft;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class DraftDeleteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_access_to_delete_method_for_no_authenticated_users()
    {
        $this->deleteJson(route('body-shop.drafts.delete', 'contact'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_delete_exist_draft()
    {
        $this->loginAsBodyShopSuperAdmin();

        $body = [
            'field1' => 'some text for field1',
            'field2' => 'some text for field2',
        ];

        $user = ['user_id' => $this->authenticatedUser->id];


        $path = 'somestring';

        $this->assertDatabaseMissing(
            Draft::TABLE_NAME,
            $user + ['path' => $path]
        );

        Draft::factory()
            ->create(
                [
                    'path' => $path
                ] + [
                    'body' => $body
                ] + $user
            );

        $this->assertDatabaseHas(
            Draft::TABLE_NAME,
            $user + ['path' => $path]
        );

        $this->deleteJson(route('body-shop.drafts.delete', $path))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            Draft::TABLE_NAME,
            $user + ['path' => $path]
        );
    }

    public function test_it_try_to_delete_not_exists_draft()
    {
        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.drafts.delete', ['someKey']))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
