<?php

namespace Tests\Feature\Api\Report\Files;

use App\Models\Image;

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

class CreateFilesTest extends TestCase
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
    public function success_create_all_image_modules()
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
                UploadedFile::fake()->image(Image::WORKING_START."_2.jpg"),
            ],
            Image::WORKING_END => [
                UploadedFile::fake()->image(Image::WORKING_END."_1.jpg"),
                UploadedFile::fake()->image(Image::WORKING_END."_2.jpg"),
            ],
            Image::EQUIPMENT => [
                UploadedFile::fake()->image(Image::EQUIPMENT."_1.jpg"),
                UploadedFile::fake()->image(Image::EQUIPMENT."_2.jpg"),
            ],
            Image::ME => [
                UploadedFile::fake()->image(Image::ME."_1.jpg"),
                UploadedFile::fake()->image(Image::ME."_2.jpg"),
            ],
            Image::OTHERS => [
                UploadedFile::fake()->image(Image::OTHERS."_1.jpg"),
                UploadedFile::fake()->image(Image::OTHERS."_2.jpg"),
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
                    [
                        'module' => Image::WORKING_START,
                        'basename' => Image::WORKING_START."_2.jpg",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                    [
                        'module' => Image::WORKING_END,
                        'basename' => Image::WORKING_END."_1.jpg",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                    [
                        'module' => Image::WORKING_END,
                        'basename' => Image::WORKING_END."_2.jpg",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                    [
                        'module' => Image::EQUIPMENT,
                        'basename' => Image::EQUIPMENT."_1.jpg",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                    [
                        'module' => Image::EQUIPMENT,
                        'basename' => Image::EQUIPMENT."_2.jpg",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                    [
                        'module' => Image::ME,
                        'basename' => Image::ME."_1.jpg",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                    [
                        'module' => Image::ME,
                        'basename' => Image::ME."_2.jpg",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                    [
                        'module' => Image::OTHERS,
                        'basename' => Image::OTHERS."_1.jpg",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                    [
                        'module' => Image::OTHERS,
                        'basename' => Image::OTHERS."_2.jpg",
                        'photo_created' => null,
                        'coords' => null,
                    ],
                    [
                        'module' => Image::SIGNATURE,
                        'basename' => null,
                        'photo_created' => null,
                        'coords' => null,
                    ]
                ]
            ]))
            ->assertJsonCount(11, 'data.images')
        ;

        $id = data_get($res, 'data.id');
        Storage::disk('public')->assertExists("report/{$id}/". Image::WORKING_START."_1.jpg");
        Storage::disk('public')->assertExists("report/{$id}/". Image::WORKING_START."_2.jpg");
        Storage::disk('public')->assertExists("report/{$id}/". Image::WORKING_END."_1.jpg");
        Storage::disk('public')->assertExists("report/{$id}/". Image::WORKING_END."_2.jpg");
        Storage::disk('public')->assertExists("report/{$id}/". Image::EQUIPMENT."_1.jpg");
        Storage::disk('public')->assertExists("report/{$id}/". Image::EQUIPMENT."_2.jpg");
        Storage::disk('public')->assertExists("report/{$id}/". Image::ME."_1.jpg");
        Storage::disk('public')->assertExists("report/{$id}/". Image::ME."_2.jpg");
        Storage::disk('public')->assertExists("report/{$id}/". Image::OTHERS."_1.jpg");
        Storage::disk('public')->assertExists("report/{$id}/". Image::OTHERS."_2.jpg");
    }
}
