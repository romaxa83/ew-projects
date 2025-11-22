<?php

namespace Tests\Feature\Mutations\FrontOffice\Commercial\CommercialQuotes;

use App\GraphQL\Mutations\FrontOffice\Commercial\CommercialQuotes\CommercialQuoteCreateMutation;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialQuote;
use App\Models\Technicians\Technician;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class CreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use TestStorage;

    public const MUTATION = CommercialQuoteCreateMutation::NAME;

    /**
     * @test
     * @throws FileNotFoundException
     */
    public function success_create(): void
    {
        $this->fakeMediaStorage();

        $technician = $this->loginAsTechnicianWithRole();

        $project = CommercialProject::factory()
            ->forMember($technician)
            ->create();

        $data = [
            'email' => $this->faker->safeEmail,
            'shipping_address' => $this->faker->city,
            'project_id' => $project->id,
        ];

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: Upload!) {%s (quote: {email: \"%s\", shipping_address: \"%s\", project_id:%s, file: $media}) {id} }"}',
                self::MUTATION,
                $data['email'],
                $data['shipping_address'],
                $data['project_id'],
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => $this->getSamplePdf(),
        ];

        $res = $this->postGraphQlUpload($attributes)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        'id'
                    ]
                ]
            ]);

        $id = $res->json('data.'.self::MUTATION.'.id');

        $model = CommercialQuote::find($id);

        $this->assertEquals($model->email, $data['email']);
        $this->assertEquals($model->shipping_address, $data['shipping_address']);
        $this->assertEquals($model->commercialProject->id, $data['project_id']);
        $this->assertNotEmpty($model->media);
    }

    /** @test */
    public function fail_technic_not_have_certificate(): void
    {
        $this->fakeMediaStorage();

        $technician = $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $project = CommercialProject::factory()
            ->forMember($technician)
            ->create();

        $data = [
            'email' => $this->faker->safeEmail,
            'shipping_address' => $this->faker->city,
            'project_id' => $project->id,
        ];

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: Upload!) {%s (quote: {email: \"%s\", shipping_address: \"%s\", project_id:%s, file: $media}) {id} }"}',
                self::MUTATION,
                $data['email'],
                $data['shipping_address'],
                $data['project_id'],
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => $this->getSamplePdf(),
        ];

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }
}
