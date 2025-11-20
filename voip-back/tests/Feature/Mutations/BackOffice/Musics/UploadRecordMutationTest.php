<?php

namespace Tests\Feature\Mutations\BackOffice\Musics;

use App\IPTelephony\Events\Queue\QueueUpdateMusicEvent;
use App\IPTelephony\Listeners\Queue\QueueUpdateMusicListener;
use App\Models\Musics\Music;
use App\Services\FTP\FTPClient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Builders\Musics\MusicBuilder;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;
use App\GraphQL\Mutations\BackOffice;
class UploadRecordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = BackOffice\Musics\MusicUploadRecordMutation::NAME;

    protected MusicBuilder $musicBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->musicBuilder = resolve(MusicBuilder::class);
    }

    /** @test */
    public function success_upload(): void
    {
        Event::fake([
            QueueUpdateMusicEvent::class,
        ]);

        $this->mock(FTPClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->andReturnTrue();
        });

        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->create();

        $data = [
            'id' => $model->id,
        ];

        $media = $this->getAudioFile();

        $this->assertTrue($model->isActive());

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: Upload!) {%s (id: \"%s\", media: $media) {id}}"}',
                self::MUTATION,
                data_get($data, 'id'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => $media,
        ];

        $this->assertEmpty($model->media);

        $this->postGraphQlBackOfficeUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        "id" => $model->id
                    ]
                ]
            ])
        ;

        $model->refresh();

        $this->assertNotEmpty($model->media);

        Event::assertDispatched(fn (QueueUpdateMusicEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            QueueUpdateMusicEvent::class,
            QueueUpdateMusicListener::class)
        ;
    }

//{"query": "mutation ($media: Upload!) {MusicUploadRecord (id: \"1\", media: $media)  {id}}"}

    /** @test */
    public function success_upload_second(): void
    {
        Event::fake([
            QueueUpdateMusicEvent::class,
        ]);

        $this->mock(FTPClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->andReturnTrue();
            $mock->shouldReceive('exist')->andReturnFalse();
        });

        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->active(false)->create();
        $model->addMedia($this->getAudioFile())
            ->toMediaCollection(Music::MEDIA_COLLECTION_NAME);

        $data = [
            'id' => $model->id,
        ];

        $media = $this->getAudioFile('new_music.mp3');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: Upload!) {%s (id: \"%s\", media: $media) {id}}"}',
                self::MUTATION,
                data_get($data, 'id'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => $media,
        ];

        $model->refresh();

        $this->assertFalse($model->isActive());
        $this->assertNotEquals($media->name, $model->media->first()->file_name);

        $this->postGraphQlBackOfficeUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        "id" => $model->id
                    ]
                ]
            ])
        ;

        $model->refresh();

        $this->assertEquals($media->name, $model->media->first()->file_name);

        $this->assertCount(1, $model->media);

        Event::assertNotDispatched(QueueUpdateMusicEvent::class);
    }

    /** @test */
    public function fail_upload_is_hold_state(): void
    {
        $this->mock(FTPClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->andReturnTrue();
        });

        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->hold()->create();

        $data = [
            'id' => $model->id,
        ];

        $media = $this->getAudioFile();

        $this->assertTrue($model->isHoldState());

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: Upload!) {%s (id: \"%s\", media: $media) {id}}"}',
                self::MUTATION,
                data_get($data, 'id'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => $media,
        ];

        $this->assertEmpty($model->media);

        $res = $this->postGraphQlBackOfficeUpload($attributes)
        ;

        self::assertErrorMessage($res, __('exceptions.music.hold'));
    }
}


