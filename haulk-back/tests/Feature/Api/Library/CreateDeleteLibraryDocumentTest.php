<?php

namespace Tests\Feature\Api\Library;

use App\Broadcasting\Events\Library\CreateLibraryBroadcast;
use App\Broadcasting\Events\Library\DeleteLibraryBroadcast;
use App\Events\ModelChanged;
use App\Models\Library\LibraryDocument;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class CreateDeleteLibraryDocumentTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_if_document_can_be_created_by_dispatcher(): void
    {
        $this->loginAsCarrierDispatcher();

        Event::fake([
            CreateLibraryBroadcast::class,
            ModelChanged::class,
            DeleteLibraryBroadcast::class
        ]);

        $response = $this->postJson(
            route('library.store'),
            [
                'document' => UploadedFile::fake()
                    ->createWithContent(
                        'beacon_shipping_logistics_1.pdf',
                        file_get_contents(base_path() . '/tests/Data/Files/Pdf/BeaconShippingLogistics/beacon_shipping_logistics_1.pdf')
                    ),
            ]
        );

        $response->assertCreated();

        Event::assertDispatched(CreateLibraryBroadcast::class);
        Event::assertDispatched(ModelChanged::class);

        $document = $response->json('data');

        $this->assertDatabaseHas(
            LibraryDocument::class,
            [
                'id' => $document['id'],
                'user_id' => null,
            ]
        );

        // delete document
        $response = $this->deleteJson(route('library.destroy', $document['id']));
        // check if deleted
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        Event::assertDispatched(DeleteLibraryBroadcast::class);
        Event::assertDispatched(ModelChanged::class);


        // check if missing in db
        $this->assertDatabaseMissing(
            'contacts',
            [
                'id' => $document['id']
            ]
        );
    }

    public function test_if_document_can_be_created_by_admin(): void
    {
        $this->loginAsCarrierSuperAdmin();

        Event::fake([
            CreateLibraryBroadcast::class,
            ModelChanged::class,
            DeleteLibraryBroadcast::class
        ]);

        $response = $this->postJson(
            route('library.store'),
            [
                'document' => UploadedFile::fake()
                    ->createWithContent(
                        'beacon_shipping_logistics_1.pdf',
                        file_get_contents(base_path() . '/tests/Data/Files/Pdf/BeaconShippingLogistics/beacon_shipping_logistics_1.pdf')
                    ),
            ]
        );

        Event::assertDispatched(CreateLibraryBroadcast::class);
        Event::assertDispatched(ModelChanged::class);

        $response->assertStatus(Response::HTTP_CREATED);

        $document = $response->json('data');

        $this->assertDatabaseHas(
            'library_documents',
            [
                'id' => $document['id'],
                'user_id' => null,
            ]
        );

        $response = $this->deleteJson(route('library.destroy', $document['id']));

        Event::assertDispatched(DeleteLibraryBroadcast::class);
        Event::assertDispatched(ModelChanged::class);

        $response->assertNoContent();

        $this->assertDatabaseMissing(
            'contacts',
            [
                'id' => $document['id']
            ]
        );
    }

    public function test_if_document_can_be_created_by_driver(): void
    {
        $this->loginAsCarrierDriver();

        Event::fake([
            CreateLibraryBroadcast::class,
            ModelChanged::class,
            DeleteLibraryBroadcast::class
        ]);

        $response = $this->postJson(
            route('library.store'),
            [
                'document' => UploadedFile::fake()
                    ->createWithContent(
                        'beacon_shipping_logistics_1.pdf',
                        file_get_contents(base_path() . '/tests/Data/Files/Pdf/BeaconShippingLogistics/beacon_shipping_logistics_1.pdf')
                    ),
            ]
        );

        Event::assertDispatched(CreateLibraryBroadcast::class);
        Event::assertDispatched(ModelChanged::class);

        $response->assertStatus(Response::HTTP_CREATED);

        $document = $response->json('data');

        $this->assertDatabaseHas(
            'library_documents',
            [
                'id' => $document['id'],
                'user_id' => null,
            ]
        );
        // delete document
        $response = $this->deleteJson(route('library.destroy', $document['id']));

        Event::assertDispatched(DeleteLibraryBroadcast::class);
        Event::assertDispatched(ModelChanged::class);

        // check if deleted
        $response->assertNoContent();

        // check if missing in db
        $this->assertDatabaseMissing(
            'contacts',
            [
                'id' => $document['id']
            ]
        );
    }

    public function test_if_document_can_be_created_by_accountant(): void
    {
        $this->loginAsCarrierAccountant();
        $driver = $this->driverFactory();

        Event::fake([
            CreateLibraryBroadcast::class,
            ModelChanged::class
        ]);

        $response = $this->postJson(
            route('library.store'),
            [
                'document' => UploadedFile::fake()
                    ->createWithContent(
                        'beacon_shipping_logistics_1.pdf',
                        file_get_contents(base_path() . '/tests/Data/Files/Pdf/BeaconShippingLogistics/beacon_shipping_logistics_1.pdf')
                    ),
                'user_id' => $driver->id,
            ]
        );

        Event::assertNotDispatched(CreateLibraryBroadcast::class);
        Event::assertNotDispatched(ModelChanged::class);

        $response->assertForbidden();
    }
}
