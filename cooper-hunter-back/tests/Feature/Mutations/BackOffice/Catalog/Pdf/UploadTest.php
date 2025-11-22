<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Pdf;

use App\GraphQL\Mutations\BackOffice\Catalog\Pdf\PdfUploadMutation;
use App\Models\Catalog\Pdf\Pdf;
use App\Models\Media\Media;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class UploadTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = PdfUploadMutation::NAME;

    public function test_it_can_attach_image(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        $file = $this->getSamplePdf();

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($pdf: Upload!) {%s (pdf: $pdf) {id} }"}',
                self::MUTATION,
            ),
            'map' => '{ "pdf": ["variables.pdf"] }',
            'pdf' => $file,
        ];

        $res = $this->postGraphQLBackOfficeUpload($attributes)
            ->assertOk()
        ;

        $this->assertDatabaseHas(Media::TABLE, ['model_type' => Pdf::class, 'model_id' => $res->json('data.pdfUpload.id')]);
    }
}

