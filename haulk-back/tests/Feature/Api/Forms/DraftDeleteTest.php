<?php

namespace Tests\Feature\Api\Forms;

use App\Models\Forms\Draft;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class DraftDeleteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_access_to_delete_method_for_no_authenticated_users()
    {
        $this->deleteJson(route('draft.delete', 'contact'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_delete_exist_draft()
    {
        $this->loginAsCarrierDispatcher();

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

        $this->deleteJson(route('draft.delete', $path))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            Draft::TABLE_NAME,
            $user + ['path' => $path]
        );
    }

    public function test_it_try_to_delete_not_exists_draft()
    {
        $this->loginAsCarrierDispatcher();
        $this->deleteJson(route('draft.delete', ['someKey']))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
