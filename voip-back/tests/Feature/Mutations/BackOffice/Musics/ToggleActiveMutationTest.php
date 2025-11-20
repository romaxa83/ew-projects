<?php

namespace Tests\Feature\Mutations\BackOffice\Musics;

use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Events\Queue\QueueDeleteMusicEvent;
use App\IPTelephony\Events\Queue\QueueUpdateMusicEvent;
use App\IPTelephony\Listeners\Queue\QueueDeleteMusicListener;
use App\IPTelephony\Listeners\Queue\QueueUpdateMusicListener;
use App\Models\Musics\Music;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Musics\MusicBuilder;
use Tests\TestCase;

class ToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    protected MusicBuilder $musicBuilder;

    public const MUTATION = BackOffice\Musics\MusicToggleActiveMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->musicBuilder = resolve(MusicBuilder::class);
    }

    /** @test */
    public function success_toggle_to_inactive(): void
    {
        Event::fake([
            QueueDeleteMusicEvent::class
        ]);

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->create();

        $this->assertTrue($model->active);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'active' => false,
                    ],
                ]
            ])
        ;

        Event::assertNotDispatched(QueueDeleteMusicEvent::class);
    }

    /** @test */
    public function success_toggle_to_inactive_with_record(): void
    {
        Event::fake([
            QueueDeleteMusicEvent::class
        ]);

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->withRecord()->create();

        $this->assertTrue($model->active);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'active' => false,
                    ],
                ]
            ])
        ;

        Event::assertDispatched(fn (QueueDeleteMusicEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            QueueDeleteMusicEvent::class,
            QueueDeleteMusicListener::class)
        ;
    }

    /** @test */
    public function success_toggle_to_active(): void
    {
        Event::fake([
            QueueUpdateMusicEvent::class
        ]);

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->active(false)->create();

        $this->assertFalse($model->active);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'active' => true,
                    ],
                ]
            ])
        ;

        Event::assertNotDispatched(QueueUpdateMusicEvent::class);
    }

    /** @test */
    public function success_toggle_to_active_with_record(): void
    {
        Event::fake([
            QueueUpdateMusicEvent::class
        ]);

        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->active(false)->withRecord()->create();

        $this->assertFalse($model->active);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'active' => true,
                    ],
                ]
            ])
        ;

        Event::assertDispatched(fn (QueueUpdateMusicEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            QueueUpdateMusicEvent::class,
            QueueUpdateMusicListener::class)
        ;
    }

    /** @test */
    public function fail_toggle_is_hold_state(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->hold()->create();

        $this->assertTrue($model->isHoldState());

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        self::assertErrorMessage($res, __('exceptions.music.hold'));
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
    }

    protected function getQueryStr(int $id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                ) {
                    id
                    active
                }
            }',
            self::MUTATION,
            $id,
        );
    }
}

