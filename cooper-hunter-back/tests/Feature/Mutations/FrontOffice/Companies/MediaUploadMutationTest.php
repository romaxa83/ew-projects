<?php

namespace Tests\Feature\Mutations\FrontOffice\Companies;

use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\Mutations\FrontOffice\Companies\MediaUploadMutation;
use App\Listeners\Companies\SendDataToOnecListeners;
use App\Models\Companies\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class MediaUploadMutationTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = MediaUploadMutation::NAME;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_upload(): void
    {
        $this->fakeMediaStorage();
        Event::fake([CreateOrUpdateCompanyEvent::class]);
        $this->loginAsDealerWithRole();

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $data = [
            'id' => $company->id,
        ];

        $image1 = UploadedFile::fake()->image('product1.jpg');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: Upload!) {%s (id: \"%s\", media: $media) {id}}"}',
                self::MUTATION,
                data_get($data, 'id'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => $image1,
        ];

        $this->assertEmpty($company->media);

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        "id" => $company->id
                    ]
                ]
            ])
        ;

        $company->refresh();

        $this->assertNotEmpty($company->media);

        Event::assertDispatched(function (CreateOrUpdateCompanyEvent $event) use ($company) {
            return $event->getCompany()->id === $company->id;
        });
        Event::assertListening(CreateOrUpdateCompanyEvent::class, SendDataToOnecListeners::class);
    }

    /** @test */
    public function success_add_new_image(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsDealerWithRole();

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $company->addMedia(
            UploadedFile::fake()->image('category1.png')
        )
            ->toMediaCollection(Company::MEDIA_COLLECTION_NAME);

        $data = [
            'id' => $company->id,
        ];

        $image1 = UploadedFile::fake()->image('product1.jpg');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: Upload!) {%s (id: \"%s\", media: $media) {id}}"}',
                self::MUTATION,
                data_get($data, 'id'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => $image1,
        ];

        $company->refresh();
        $this->assertCount(1, $company->media);

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        "id" => $company->id
                    ]
                ]
            ])
        ;

        $company->refresh();

        $this->assertCount(2, $company->media);
    }
}

