<?php


namespace Commands;


use App\Models\Library\LibraryDocument;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DeleteLostMediaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_delete_lost_media_leave_rest(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $response = $this->postJson(
            route('library.store'),
            [
                'document' => UploadedFile::fake()->image('some_name1.jpg'),
            ]
        )
            ->assertCreated();

        $document1 = $response->json('data');

        $response = $this->postJson(
            route('library.store'),
            [
                'document' => UploadedFile::fake()->image('some_name2.jpg'),
            ]
        )
            ->assertCreated();

        $document2 = $response->json('data');

        $this->assertDatabaseHas(config('medialibrary.table_name'), ['model_id' => $document1['id']]);
        $this->assertDatabaseHas(config('medialibrary.table_name'), ['model_id' => $document2['id']]);

        LibraryDocument::where('id', $document1['id'])
            ->delete();

        $this->assertDatabaseHas(config('medialibrary.table_name'), ['model_id' => $document1['id']]);
        $this->assertDatabaseHas(config('medialibrary.table_name'), ['model_id' => $document2['id']]);

        $this->artisan('media:delete-lost')
            ->assertExitCode(0);

        $this->assertDatabaseMissing(config('medialibrary.table_name'), ['model_id' => $document1['id']]);
        $this->assertDatabaseHas(config('medialibrary.table_name'), ['model_id' => $document2['id']]);
    }
}
