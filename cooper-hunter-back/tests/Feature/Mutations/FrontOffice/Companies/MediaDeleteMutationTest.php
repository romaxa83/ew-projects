<?php

namespace Tests\Feature\Mutations\FrontOffice\Companies;

use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\Mutations\FrontOffice\Companies\MediaDeleteMutation;
use App\Listeners\Companies\SendDataToOnecListeners;
use App\Models\Companies\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class MediaDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = MediaDeleteMutation::NAME;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        $this->fakeMediaStorage();
        Event::fake([CreateOrUpdateCompanyEvent::class]);
        $this->loginAsDealerWithRole();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company->addMedia(
            UploadedFile::fake()->image('category1.png')
        )
            ->toMediaCollection(Company::MEDIA_COLLECTION_NAME);

        $company->refresh();

        $this->postGraphQL([
            'query' => $this->getQueryStrDelete($company->media->first()->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $company->id
                    ]
                ]
            ])
        ;

        $company->refresh();
        $this->assertEmpty($company->media);

        Event::assertDispatched(function (CreateOrUpdateCompanyEvent $event) use ($company) {
            return $event->getCompany()->id === $company->id;
        });
        Event::assertListening(CreateOrUpdateCompanyEvent::class, SendDataToOnecListeners::class);
    }

    protected function getQueryStrDelete($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                ) {
                    id
                }
            }',
            self::MUTATION,
            $id,
        );
    }
}
