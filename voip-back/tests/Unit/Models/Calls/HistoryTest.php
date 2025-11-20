<?php

namespace Tests\Unit\Models\Calls;

use App\Enums\Calls\HistoryStatus;
use App\Enums\Formats\DatetimeEnum;
use App\Models\Calls\History;
use Tests\Builders\Calls\HistoryBuilder;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    protected HistoryBuilder $historyBuilder;
    public function setUp(): void
    {
        parent::setUp();
        $this->historyBuilder = resolve(HistoryBuilder::class);
    }

    /** @test */
    public function has_audio_record_status_answered(): void
    {
        /** @var $model History */
        $model = $this->historyBuilder->setStatus(HistoryStatus::ANSWERED())->create();

        $baseUrl = config('asterisk.call_record_url');
        $this->assertEquals(
            $model->getUrlAudioRecord(),
            "{$baseUrl}/{$model->call_date->format(DatetimeEnum::DATE_SLASH)}/{$model->uniqueid}.mp3"
        );
    }

    /** @test */
    public function has_audio_record_status_transfer(): void
    {
        /** @var $model History */
        $model = $this->historyBuilder->setStatus(HistoryStatus::TRANSFER())->create();

        $baseUrl = config('asterisk.call_record_url');
        $this->assertEquals(
            $model->getUrlAudioRecord(),
            "{$baseUrl}/{$model->call_date->format(DatetimeEnum::DATE_SLASH)}/{$model->uniqueid}.mp3"
        );
    }

    /** @test */
    public function no_has_audio_record_status_busy(): void
    {
        /** @var $model History */
        $model = $this->historyBuilder->setStatus(HistoryStatus::BUSY())->create();

        $this->assertNull($model->getUrlAudioRecord());
    }

    /** @test */
    public function no_has_audio_record_status_cancel(): void
    {
        /** @var $model History */
        $model = $this->historyBuilder->setStatus(HistoryStatus::CANCEL())->create();

        $this->assertNull($model->getUrlAudioRecord());
    }

    /** @test */
    public function no_has_audio_record_status_no_answer(): void
    {
        /** @var $model History */
        $model = $this->historyBuilder->setStatus(HistoryStatus::NO_ANSWER())->create();

        $this->assertNull($model->getUrlAudioRecord());
    }

    /** @test */
    public function no_has_audio_record_status_congestion(): void
    {
        /** @var $model History */
        $model = $this->historyBuilder->setStatus(HistoryStatus::CONGESTION())->create();

        $this->assertNull($model->getUrlAudioRecord());
    }

    /** @test */
    public function no_has_audio_record_status_chanunavail(): void
    {
        /** @var $model History */
        $model = $this->historyBuilder->setStatus(HistoryStatus::CHANUNAVAIL())->create();

        $this->assertNull($model->getUrlAudioRecord());
    }
}

