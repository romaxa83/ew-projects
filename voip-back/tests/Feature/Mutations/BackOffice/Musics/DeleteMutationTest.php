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

class DeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected MusicBuilder $musicBuilder;

    public const MUTATION = BackOffice\Musics\MusicDeleteMutation::NAME;

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

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $this->assertNull(Music::find($model->id));

        Event::assertNotDispatched(QueueDeleteMusicEvent::class);
    }

    /** @test */
    public function success_delete_with_record(): void
    {
        Event::fake([
            QueueDeleteMusicEvent::class
        ]);

        $this->mock(FTPClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('exist')->andReturnTrue();
            $mock->shouldReceive('delete')->andReturnTrue();
        });

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->withRecord()->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $this->assertNull(Music::find($model->id));

        Event::assertDispatched(fn (QueueDeleteMusicEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            QueueDeleteMusicEvent::class,
            QueueDeleteMusicListener::class)
        ;
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Music */
        $model = $this->musicBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertUnauthorized($res);

        $this->assertNotNull(Music::find($model->id));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertPermission($res);

        $this->assertNotNull(Music::find($model->id));
    }

    protected function getQueryStr(int $id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                )
            }',
            self::MUTATION,
            $id
        );
    }
}
