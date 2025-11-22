<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Pdf;

use App\GraphQL\Mutations\BackOffice\Catalog\Pdf\PdfReUploadMutation;
use Database\Factories\Catalog\Pdf\PdfFactory;
use Database\Factories\Media\MediaFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class ReUploadTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = PdfReUploadMutation::NAME;

    /** @test */
    public function re_upload(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        $file = $this->getSamplePdf();

        $model = PdfFactory::new()->create();
        $path = $model->path;
        $media = MediaFactory::new([
            'model_type' => $model::class,
            'model_id' => $model->id,
        ])->create();

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($pdf: Upload!) {%s (id:%s, pdf: $pdf) {id} }"}',
                self::MUTATION,
                $model->id
            ),
            'map' => '{ "pdf": ["variables.pdf"] }',
            'pdf' => $file,
        ];

        $res = $this->postGraphQLBackOfficeUpload($attributes)
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                    ]
                ]
            ])
        ;

        $model->refresh();

        $this->assertEquals($path, $model->path);
        $this->assertEquals($media->model_id, $model->media[0]->model_id);
        $this->assertEquals($media->file_name, $model->media[0]->file_name);
    }
}
