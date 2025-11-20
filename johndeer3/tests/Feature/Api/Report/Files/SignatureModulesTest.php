<?php

namespace Tests\Feature\Api\Report\Files;

use App\Models\Image;

use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\Feature\Api\Report\Create\CreateTest;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class SignatureModulesTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $featureBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
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
            Image::SIGNATURE => [
                "[23,345,6,3,2,435,56]",
            ]
        ];

        $res = $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                'images' => [
                    [
                        'module' => Image::SIGNATURE,
                        'basename' => Image::SIGNATURE.".png",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                ]
            ]))
            ->assertJsonCount(1, 'data.images')
        ;

        $id = data_get($res, 'data.id');
        $url = "report/{$id}/". Image::SIGNATURE.".png";

        Storage::disk('public')->assertExists($url);

        $report = Report::query()->where('id', $id)->first();

        $this->assertTrue($report->hasSignature());

        $imageModel = $report->images->first();
        $this->assertNull($imageModel->lat);
        $this->assertNull($imageModel->lon);
        $this->assertNull($imageModel->photo_created_at);
        $this->assertNull($imageModel->metadata);
        $this->assertEquals($imageModel->model, Image::SIGNATURE);
        $this->assertEquals($imageModel->url, $url);
        $this->assertEquals(Image::getUrl($imageModel->url) , env('APP_URL') . '/storage/'. $url);
    }

    /** @test */
    public function success_update_report()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::SIGNATURE => [
                "[23,345,6,3,2,435,56]",
            ]
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::OPEN_EDIT)
            ->setUser($user)
            ->create();

        $rep->refresh();
        $this->assertFalse($rep->hasSignature());

        $res = $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                'images' => [
                    [
                        'module' => Image::SIGNATURE,
                        'basename' => Image::SIGNATURE.".png",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                ]
            ]))
            ->assertJsonCount(1, 'data.images')
        ;

        $id = data_get($res, 'data.id');
        $url = "report/{$id}/". Image::SIGNATURE.".png";

        Storage::disk('public')->assertExists($url);

        $report = Report::query()->where('id', $id)->first();

        $this->assertTrue($report->hasSignature());

        $imageModel = $report->images->first();
        $this->assertNull($imageModel->lat);
        $this->assertNull($imageModel->lon);
        $this->assertNull($imageModel->photo_created_at);
        $this->assertNull($imageModel->metadata);
        $this->assertEquals($imageModel->model, Image::SIGNATURE);
        $this->assertEquals($imageModel->url, $url);
        $this->assertEquals($imageModel->url, $url);
        $this->assertEquals(Image::getUrl($imageModel->url) , env('APP_URL') . '/storage/'. $url);
    }

    /** @test */
    public function success_not_update_if_exist()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::SIGNATURE => [
                "[23,345,6,3,2,435,56]",
            ]
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::OPEN_EDIT)
            ->setUser($user)
            ->create();
        $signature = new Image();
        $signature->model = Image::SIGNATURE;
        $signature->entity_type = Report::class;
        $signature->entity_id = $rep->id;
        $signature->url = "some_url";
        $signature->basename = 'mySignature';
        $signature->save();

        $rep->refresh();
        $this->assertTrue($rep->hasSignature());

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                'images' => [
                    [
                        'module' => Image::SIGNATURE,
                        'basename' => $signature->basename,
                        'photo_created' => null,
                        'coords' => null,
                        'url' => env('APP_URL') . '/storage/'. $signature->url
                    ],
                ]
            ]))
            ->assertJsonCount(1, 'data.images')
        ;

        $report = Report::query()->where('id', $rep->id)->first();
        $imageModel = $report->images->first();

        $this->assertEquals($imageModel->model, Image::SIGNATURE);
        $this->assertEquals($imageModel->url, $signature->url);
        $this->assertEquals($imageModel->basename, $signature->basename);
    }

    /** @test */
    public function success_empty_data()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::SIGNATURE => []
        ];

        $res = $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ]);

        $id = data_get($res, 'data.id');
        $url = "report/{$id}/". Image::SIGNATURE.".png";

        Storage::disk('public')->assertMissing($url);

        $report = Report::query()->where('id', $id)->first();

        $imageModel = $report->images->first();
        $this->assertNull($imageModel);
    }

    /** @test */
    public function fail_empty_byte()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::SIGNATURE => [
                "",
            ]
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse("byteData empty for signature"))
        ;
    }

    /** @test */
    public function fail_not_string_byte()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['files'] = [
            Image::SIGNATURE => [
                [23,345,6,3,2,435,56],
            ]
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse(["The files.signature.0 must be a string."]))
        ;
    }
}

