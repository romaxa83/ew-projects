<?php

namespace Tests\Feature\Mutations\BackOffice\Musics;

use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Events\Queue\QueueDeleteMusicEvent;
use App\IPTelephony\Listeners\Queue\QueueDeleteMusicListener;
use App\Models\Musics\Music;
use App\Services\FTP\FTPClient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Builders\Musics\MusicBuilder;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class DeleteRecordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use TestStorage;

    protected MusicBuilder $musicBuilder;

    public const MUTATION = BackOffice\Musics\MusicDeleteRecordMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->musicBuilder = resolve(MusicBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        Event::fake([
            QueueDeleteMusicEvent::class
        ]);

        $this->mock(FTPClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('exist')->andReturnFalse();
        });

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->create();
        $model->addMedia(
            $this->getAudioFile()
        )
            ->toMediaCollection(Music::MEDIA_COLLECTION_NAME);

        $model->refresh();

        $this->assertNotEmpty($model->media);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->media->first()->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id
                    ],
                ]
            ])
        ;

        $model->refresh();

        $this->assertEmpty($model->media);

        Event::assertDispatched(fn (QueueDeleteMusicEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            QueueDeleteMusicEvent::class,
            QueueDeleteMusicListener::class)
        ;
    }

    /** @test */
    public function fail_delete_is_hold_state(): void
    {
        $this->mock(FTPClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('exist')->andReturnFalse();
        });

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->hold()->create();
        $model->addMedia(
            $this->getAudioFile()
        )
            ->toMediaCollection(Music::MEDIA_COLLECTION_NAME);

        $model->refresh();

        $this->assertTrue($model->isHoldState());

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->media->first()->id)
        ])
        ;

        self::assertErrorMessage($res, __('exceptions.music.hold'));
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Music */
        $model = $this->musicBuilder->create();
        $model->addMedia(
            $this->getAudioFile()
        )
            ->toMediaCollection(Music::MEDIA_COLLECTION_NAME);

        $model->refresh();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->media->first()->id)
        ])
        ;

        $this->assertUnauthorized($res);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->create();
        $model->addMedia(
            $this->getAudioFile()
        )
            ->toMediaCollection(Music::MEDIA_COLLECTION_NAME);

        $model->refresh();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->media->first()->id)
        ])
        ;

        $this->assertPermission($res);
    }

    protected function getQueryStr(int $id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    media_id: %s
                ) {
                    id
                }
            }',
            self::MUTATION,
            $id
        );
    }
}

