<?php

namespace Tests\Feature\Mutations\FrontOffice\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerPhotoType;
use App\Enums\Commercial\Commissioning\AnswerType;
use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Enums\Commercial\Commissioning\ProtocolType;
use App\GraphQL\Mutations\FrontOffice\Commercial\Commissioning\AnswerImageUploadMutation;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\Answer;
use App\Models\Technicians\Technician;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Commercial\Commissioning\AnswerBuilder;
use Tests\Builders\Commercial\Commissioning\OptionAnswerBuilder;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolQuestionBuilder;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class AnswerImageUploadMutationTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = AnswerImageUploadMutation::NAME;

    protected $protocolBuilder;
    protected $protocolProjectBuilder;
    protected $protocolProjectQuestionBuilder;
    protected $projectBuilder;
    protected $optionAnswerBuilder;
    protected $questionBuilder;
    protected $answerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->questionBuilder = resolve(QuestionBuilder::class);
        $this->optionAnswerBuilder = resolve(OptionAnswerBuilder::class);
        $this->protocolProjectBuilder = resolve(ProjectProtocolBuilder::class);
        $this->protocolProjectQuestionBuilder = resolve(ProjectProtocolQuestionBuilder::class);
        $this->answerBuilder = resolve(AnswerBuilder::class);
    }

    /** @test */
    public function success_upload(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_REQUIRED)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $answer = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)->create();

        $data = [
            'id' => $answer->id,
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

        $this->assertEmpty($answer->media);

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        "id" => $answer->id
                    ]
                ]
            ])
        ;

        $answer->refresh();

        $this->assertNotEmpty($answer->media);
    }

    /** @test */
    public function success_add_new_image(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_REQUIRED)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $answer = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)->create();
        $answer->addMedia(
            UploadedFile::fake()->image('category1.png')
        )
            ->toMediaCollection(Answer::MEDIA_COLLECTION_NAME);

        $data = [
            'id' => $answer->id,
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

        $answer->refresh();
        $this->assertCount(1, $answer->media);

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        "id" => $answer->id
                    ]
                ]
            ])
        ;

        $answer->refresh();

        $this->assertCount(2, $answer->media);
    }

    /** @test */
    public function fail_technic_not_have_certificate(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_REQUIRED)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $answer = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)->create();
        $answer->addMedia(
            UploadedFile::fake()->image('category1.png')
        )
            ->toMediaCollection(Answer::MEDIA_COLLECTION_NAME);

        $data = [
            'id' => $answer->id,
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

        $answer->refresh();
        $this->assertCount(1, $answer->media);

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }
}

