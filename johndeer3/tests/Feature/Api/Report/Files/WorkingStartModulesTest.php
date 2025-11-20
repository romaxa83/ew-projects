<?php

namespace Tests\Feature\Api\Report\Files;

use App\Models\Image;

use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\UserBuilder;
use Tests\Feature\Api\Report\Create\CreateTest;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class WorkingStartModulesTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image(Image::WORKING_START."_1.jpg"),
            ]
        ];

        $res = $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                'images' => [
                    [
                        'module' => Image::WORKING_START,
                        'basename' => Image::WORKING_START."_1.jpg",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                ]
            ]))
            ->assertJsonCount(2, 'data.images')
        ;

        $id = data_get($res, 'data.id');
        $url = "report/{$id}/". Image::WORKING_START."_1.jpg";
        Storage::disk('public')->assertExists($url);

        $report = Report::query()->where('id', $id)->first();

        $imageModel = $report->images->first();
        $this->assertNull($imageModel->lat);
        $this->assertNull($imageModel->lon);
        $this->assertNull($imageModel->photo_created_at);
        $this->assertNotNull($imageModel->metadata);
        $this->assertEquals($imageModel->model, Image::WORKING_START);
        $this->assertEquals($imageModel->url, $url);
        $this->assertEquals($imageModel->url, $url);
        $this->assertEquals(Image::getUrl($imageModel->url) , env('APP_URL') . '/storage/'. $url);
    }

    /** @test */
    public function success_as_jpeg_extension()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image(Image::WORKING_START."_1.jpeg"),
            ]
        ];

        $res = $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                'images' => [
                    [
                        'basename' => Image::WORKING_START."_1.jpeg",
                    ],
                ]
            ]))
            ->assertJsonCount(2, 'data.images')
        ;

        $id = data_get($res, 'data.id');
        $url = "report/{$id}/". Image::WORKING_START."_1.jpeg";
        Storage::disk('public')->assertExists($url);
    }

    /** @test */
    public function success_as_png_extension()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image(Image::WORKING_START."_1.png"),
            ]
        ];

        $res = $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                'images' => [
                    [
                        'basename' => Image::WORKING_START."_1.png",
                    ],
                ]
            ]))
            ->assertJsonCount(2, 'data.images')
        ;

        $id = data_get($res, 'data.id');
        $url = "report/{$id}/". Image::WORKING_START."_1.png";
        Storage::disk('public')->assertExists($url);
    }

    /** @test */
    public function fail_as_pdf_extension()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image(Image::WORKING_START."_1.pdf"),
            ]
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse([
                "The files.".Image::WORKING_START.".0 must be an image.",
                "The files.".Image::WORKING_START.".0 must be a file of type: jpeg, jpg, png."
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function fail_as_gif_extension()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image(Image::WORKING_START."_1.gif"),
            ]
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse([
                "The files.".Image::WORKING_START.".0 must be a file of type: jpeg, jpg, png.",
            ]))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function fail_more_file()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image(Image::WORKING_START."_1.png"),
                UploadedFile::fake()->image(Image::WORKING_START."_2.png"),
                UploadedFile::fake()->image(Image::WORKING_START."_3.png"),
            ]
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse([
                "The files.".Image::WORKING_START." may not have more than 2 items.",
            ]))
            ->assertJsonCount(1, 'data')
        ;
    }
}





