<?php

namespace Tests\Unit\Listeners;

use App\Events\DeactivateFeature;
use App\Listeners\RemoveDeactivateFeatureListeners;
use App\Models\Report\Report;
use App\Repositories\Report\ReportRepository;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;

class RemoveDeactivateFeatureListenerTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $reportBuilder;
    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $user = $this->userBuilder->create();

        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues('val')
            ->create();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]],
            ])
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $this->assertNotEmpty($report->features);

        $event = new DeactivateFeature($feature);
        $listener = new RemoveDeactivateFeatureListeners();
        $listener->handle($event);

        $report->refresh();

        $this->assertEmpty($report->features);
    }

    /** @test */
    public function success_some_feature(): void
    {
        $user = $this->userBuilder->create();

        $feature_1 = $this->featureBuilder
            ->withTranslation()
            ->setValues('val_1')
            ->create();
        $feature_2 = $this->featureBuilder
            ->withTranslation()
            ->setValues('val_2')
            ->create();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature_1->id, "group" => [
                    ["choiceId" => $feature_1->values[0]->id]
                ]],
                ["id" => $feature_2->id, "group" => [
                    ["choiceId" => $feature_2->values[0]->id]
                ]],
            ])
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $this->assertCount(2, $report->features);

        $event = new DeactivateFeature($feature_1);
        $listener = new RemoveDeactivateFeatureListeners();
        $listener->handle($event);

        $report->refresh();

        $this->assertCount(1, $report->features);
        $this->assertEquals($feature_2->id, $report->features[0]->feature_id);
    }

    /** @test */
    public function not_remove_report_status_created(): void
    {
        $user = $this->userBuilder->create();
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues('val')
            ->create();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])
            ->setStatus(ReportStatus::CREATED)
            ->create();

        $this->assertCount(1, $report->features);

        $event = new DeactivateFeature($feature);
        $listener = new RemoveDeactivateFeatureListeners();
        $listener->handle($event);

        $report->refresh();

        $this->assertCount(1, $report->features);
    }

    /** @test */
    public function not_remove_report_status_open_edit(): void
    {
        $user = $this->userBuilder->create();
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues('val')
            ->create();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])
            ->setStatus(ReportStatus::OPEN_EDIT)
            ->create();

        $this->assertCount(1, $report->features);

        $event = new DeactivateFeature($feature);
        $listener = new RemoveDeactivateFeatureListeners();
        $listener->handle($event);

        $report->refresh();

        $this->assertCount(1, $report->features);
    }

    /** @test */
    public function not_remove_report_status_edited(): void
    {
        $user = $this->userBuilder->create();
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues('val')
            ->create();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])
            ->setStatus(ReportStatus::EDITED)
            ->create();

        $this->assertCount(1, $report->features);

        $event = new DeactivateFeature($feature);
        $listener = new RemoveDeactivateFeatureListeners();
        $listener->handle($event);

        $report->refresh();

        $this->assertCount(1, $report->features);
    }

    /** @test */
    public function not_remove_report_status_verify(): void
    {
        $user = $this->userBuilder->create();
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues('val')
            ->create();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])
            ->setStatus(ReportStatus::VERIFY)
            ->create();

        $this->assertCount(1, $report->features);

        $event = new DeactivateFeature($feature);
        $listener = new RemoveDeactivateFeatureListeners();
        $listener->handle($event);

        $report->refresh();

        $this->assertCount(1, $report->features);
    }

    /** @test */
    public function fail_return_exception(): void
    {
        $user = $this->userBuilder->create();

        $feature = $this->featureBuilder
            ->setValues('val')
            ->create();

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]],
            ])
            ->setStatus(ReportStatus::IN_PROCESS)
            ->create();

        $this->assertNotEmpty($report->features);

        $this->mock(ReportRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getReportByFeatureAndStatus")
                ->andThrows(\Exception::class, "some exception message");
        });

        $event = new DeactivateFeature($feature);
        $listener = new RemoveDeactivateFeatureListeners();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("some exception message");

        $listener->handle($event);
    }
}
