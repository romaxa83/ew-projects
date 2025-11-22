<?php

namespace Tests\Feature\Mutations\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use Illuminate\Http\UploadedFile;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class SparesDownloadFileTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_upload()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::CALC_CATALOG_UPLOAD_SPARES])->create();
        $this->loginAsAdmin($admin);


        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {sparesDownload(type: volvo file: $file) {message status}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.xlsx')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );
//"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        $this->assertTrue($response->json('data.sparesDownload.status'));
        $this->assertEquals($response->json('data.sparesDownload.message'), __('message.calc.file with spares download'));
    }

    /** @test */
    public function wrong_extension()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::CALC_CATALOG_UPLOAD_SPARES])->create();
        $this->loginAsAdmin($admin);


        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {sparesDownload(type: volvo file: $file) {message status}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertArrayHasKey('errors', $response->json());

    }

    /** @test */
    public function wrong_not_file()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::CALC_CATALOG_UPLOAD_SPARES])->create();
        $this->loginAsAdmin($admin);


        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {sparesDownload(type: volvo file: $file) {message status}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => null
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.file empty'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function wrong_not_file_empty()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::CALC_CATALOG_UPLOAD_SPARES])->create();
        $this->loginAsAdmin($admin);


        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {sparesDownload(type: volvo file: $file) {message status}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => []
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.file empty'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }


}
