<?php

namespace Tests\Feature\Api\BodyShop\Forms;

use App\Models\Forms\Draft;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class DraftStoreTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_no_access_fot_no_authorized_users()
    {
        $this->postJson(route('body-shop.drafts.store', 'contact'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_create_new_draft()
    {
        $this->loginAsBodyShopAdmin();

        $user = ['user_id' => $this->authenticatedUser->id];
        $path = 'someKeyForDraft';
        $pathParam = ['path' => $path];

        $this->assertDatabaseMissing(Draft::TABLE_NAME, $user + $pathParam);

        $attributes = [
            'field1' => 'text1',
            'field2' => 'text2',
        ];

        $this->postJson(route('body-shop.drafts.store', $path), $attributes);

        $this->assertDatabaseHas(Draft::TABLE_NAME, $user + $pathParam);
    }

    public function test_it_update_exists_draft()
    {
        $this->loginAsBodyShopAdmin();

        $attributes = [
            'field1' => 'some text for field1',
            'field2' => 'some text for field2',
        ];

        $user = ['user_id' => $this->authenticatedUser->id];

        $path = 'someKeyForDraft';
        $pathParam = ['path' => $path];
        Draft::factory()->create($pathParam + ['body' => $attributes] + $user);

        $this->assertDatabaseHas(Draft::TABLE_NAME, $user + $pathParam);

        $attributes['field1'] = 'updated field1 data.';

        $this->postJson(route('body-shop.drafts.store', $path), $attributes);

        $this->assertDatabaseHas(Draft::TABLE_NAME, $user + $pathParam);
    }
}
