<?php

namespace Tests\Unit\Listeners;

use App\Events\FcmPushGroup;
use App\Listeners\FcmPushGroupListeners;
use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\Notification\FcmNotification;
use App\Models\Notification\FcmTemplate;
use App\Models\User\Role;
use App\Services\FcmNotification\Exception\FcmNotificationException;
use App\Services\FcmNotification\Sender\FirebaseSender;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;

class FcmPushGroupListenersTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success_planned(): void
    {
        \Config::set('firebase.enable_firebase', true);

        $this->assertEquals(0, FcmNotification::query()->count());

        $admin = $this->userBuilder->setFcmToken('some_token_admin')
            ->setLogin('admin')->create();

        $this->assertEmpty($admin->fcm_notifications);

        $role_tm = Role::query()->where('role', Role::ROLE_TM)->first();
        $tm_1 = $this->userBuilder->setRole($role_tm)->setFcmToken('some_token_tm_1')->create();
        $tm_2 = $this->userBuilder->setRole($role_tm)->setFcmToken('some_token_tm_2')->create();

        $dealer = Dealer::query()->first();
        $dealer->users()->attach([$tm_1->id, $tm_2->id]);

        $this->assertEmpty($tm_1->fcm_notifications);
        $this->assertEmpty($tm_2->fcm_notifications);

        $eg = EquipmentGroup::query()->first();

        $role_pss = Role::query()->where('role', Role::ROLE_PSS)->first();
        $pss_1 = $this->userBuilder->setRole($role_pss)->setFcmToken('some_token_pss_1')->create();
        $pss_2 = $this->userBuilder->setRole($role_pss)->setFcmToken('some_token_pss_2')->create();

        $pss_1->egs()->attach($eg);
        $pss_2->egs()->attach($eg);

        $this->assertEmpty($pss_1->fcm_notifications);
        $this->assertEmpty($pss_2->fcm_notifications);

        $user = $this->userBuilder->setDealer($dealer)
            ->setFcmToken('some_token')->create();

        $this->assertEmpty($user->fcm_notifications);

        $date = Carbon::now()->addDay();
        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->setUser($user)
            ->setMachineData([
                'equipment_group_id' => $eg->id,
            ])
            ->create();

        $template = FcmTemplate::PLANNED;

        $this->mock(FirebaseSender::class, function(MockInterface $mock){
            $mock->shouldReceive("send")
                ->andReturn(["success" => true]);
        });

        $event = new FcmPushGroup($rep, $template);
        $listener = app(FcmPushGroupListeners::class);

        $listener->handle($event);

        $admin->refresh();
        $user->refresh();
        $tm_1->refresh();
        $tm_2->refresh();
        $pss_1->refresh();
        $pss_2->refresh();

//        dd($user->fcm_notifications);

        $this->assertEquals($user->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);

        $this->assertEquals($admin->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);
        $this->assertEquals($admin->fcm_notifications->first()->action, $template);
        $this->assertNotNull($admin->fcm_notifications->first()->send_data);
        $this->assertEquals($admin->fcm_notifications->first()->response_data, ["success" => true]);

        $this->assertEquals($tm_1->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);
        $this->assertEquals($tm_1->fcm_notifications->first()->action, $template);
        $this->assertNotNull($tm_1->fcm_notifications->first()->send_data);
        $this->assertEquals($tm_1->fcm_notifications->first()->response_data, ["success" => true]);

        $this->assertEquals($tm_2->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);
        $this->assertEquals($tm_2->fcm_notifications->first()->action, $template);
        $this->assertNotNull($tm_2->fcm_notifications->first()->send_data);
        $this->assertEquals($tm_2->fcm_notifications->first()->response_data, ["success" => true]);

        $this->assertEquals($pss_1->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);
        $this->assertEquals($pss_1->fcm_notifications->first()->action, $template);
        $this->assertNotNull($pss_1->fcm_notifications->first()->send_data);
        $this->assertEquals($pss_1->fcm_notifications->first()->response_data, ["success" => true]);

        $this->assertEquals($pss_2->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);
        $this->assertEquals($pss_2->fcm_notifications->first()->action, $template);
        $this->assertNotNull($pss_2->fcm_notifications->first()->send_data);
        $this->assertEquals($pss_2->fcm_notifications->first()->response_data, ["success" => true]);

        $this->assertEquals(7, FcmNotification::query()->count());
    }

    /** @test */
    public function success_postponed(): void
    {
        \Config::set('firebase.enable_firebase', true);

        $admin = $this->userBuilder->setFcmToken('some_token_admin')
            ->setLogin('admin')->create();

        $this->assertEmpty($admin->fcm_notifications);

        $role_tm = Role::query()->where('role', Role::ROLE_TM)->first();
        $tm_1 = $this->userBuilder->setRole($role_tm)->setFcmToken('some_token_tm_1')->create();
        $tm_2 = $this->userBuilder->setRole($role_tm)->setFcmToken('some_token_tm_2')->create();

        $dealer = Dealer::query()->first();
        $dealer->users()->attach([$tm_1->id, $tm_2->id]);

        $this->assertEmpty($tm_1->fcm_notifications);
        $this->assertEmpty($tm_2->fcm_notifications);

        $eg = EquipmentGroup::query()->first();

        $role_pss = Role::query()->where('role', Role::ROLE_PSS)->first();
        $pss_1 = $this->userBuilder->setRole($role_pss)->setFcmToken('some_token_pss_1')->create();
        $pss_2 = $this->userBuilder->setRole($role_pss)->setFcmToken('some_token_pss_2')->create();

        $pss_1->egs()->attach($eg);
        $pss_2->egs()->attach($eg);

        $this->assertEmpty($pss_1->fcm_notifications);
        $this->assertEmpty($pss_2->fcm_notifications);

        $user = $this->userBuilder->setDealer($dealer)
            ->setFcmToken('some_token')->create();

        $this->assertEmpty($user->fcm_notifications);

        $date = Carbon::now()->addDay();
        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->setUser($user)
            ->setMachineData([
                'equipment_group_id' => $eg->id,
            ])
            ->create();

        $template = FcmTemplate::POSTPONED;

        $this->mock(FirebaseSender::class, function(MockInterface $mock){
            $mock->shouldReceive("send")
                ->andReturn(["success" => true]);
        });

        $event = new FcmPushGroup($rep, $template);
        $listener = app(FcmPushGroupListeners::class);

        $listener->handle($event);

        $admin->refresh();
        $user->refresh();
        $tm_1->refresh();
        $tm_2->refresh();
        $pss_1->refresh();
        $pss_2->refresh();

        $this->assertNotEmpty($user->fcm_notifications);

        $this->assertEquals($admin->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);
        $this->assertEquals($admin->fcm_notifications->first()->action, $template);
        $this->assertNotNull($admin->fcm_notifications->first()->send_data);
        $this->assertEquals($admin->fcm_notifications->first()->response_data, ["success" => true]);

        $this->assertEquals($tm_1->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);
        $this->assertEquals($tm_1->fcm_notifications->first()->action, $template);
        $this->assertNotNull($tm_1->fcm_notifications->first()->send_data);
        $this->assertEquals($tm_1->fcm_notifications->first()->response_data, ["success" => true]);

        $this->assertEquals($tm_2->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);
        $this->assertEquals($tm_2->fcm_notifications->first()->action, $template);
        $this->assertNotNull($tm_2->fcm_notifications->first()->send_data);
        $this->assertEquals($tm_2->fcm_notifications->first()->response_data, ["success" => true]);

        $this->assertEquals($pss_1->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);
        $this->assertEquals($pss_1->fcm_notifications->first()->action, $template);
        $this->assertNotNull($pss_1->fcm_notifications->first()->send_data);
        $this->assertEquals($pss_1->fcm_notifications->first()->response_data, ["success" => true]);

        $this->assertEquals($pss_2->fcm_notifications->first()->status, FcmNotification::STATUS_SEND);
        $this->assertEquals($pss_2->fcm_notifications->first()->action, $template);
        $this->assertNotNull($pss_2->fcm_notifications->first()->send_data);
        $this->assertEquals($pss_2->fcm_notifications->first()->response_data, ["success" => true]);
    }

    /** @test */
    public function success_planned_without_psss(): void
    {
        \Config::set('firebase.enable_firebase', true);

        $this->assertEquals(0, FcmNotification::query()->count());

        $admin = $this->userBuilder->setFcmToken('some_token_admin')
            ->setLogin('admin')->create();

        $role_tm = Role::query()->where('role', Role::ROLE_TM)->first();
        $tm_1 = $this->userBuilder->setRole($role_tm)->setFcmToken('some_token_tm_1')->create();

        $dealer = Dealer::query()->first();
        $dealer->users()->attach([$tm_1->id]);

        $user = $this->userBuilder->setDealer($dealer)
            ->setFcmToken('some_token')->create();

        $this->assertEmpty($user->fcm_notifications);

        $date = Carbon::now()->addDay();
        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->setUser($user)
            ->create();

        $template = FcmTemplate::PLANNED;

        $this->mock(FirebaseSender::class, function(MockInterface $mock){
            $mock->shouldReceive("send")
                ->andReturn(["success" => true]);
        });

        $event = new FcmPushGroup($rep, $template);
        $listener = app(FcmPushGroupListeners::class);

        $listener->handle($event);

        $this->assertEquals(4, FcmNotification::query()->count());
    }

    /** @test */
    public function success_planned_only_admin(): void
    {
        \Config::set('firebase.enable_firebase', true);

        $this->assertEquals(0, FcmNotification::query()->count());

        $admin = $this->userBuilder->setFcmToken('some_token_admin')
            ->setLogin('admin')->create();

        $user = $this->userBuilder
            ->setFcmToken('some_token')->create();

        $this->assertEmpty($user->fcm_notifications);

        $date = Carbon::now()->addDay();
        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->setUser($user)
            ->create();

        $template = FcmTemplate::PLANNED;

        $this->mock(FirebaseSender::class, function(MockInterface $mock){
            $mock->shouldReceive("send")
                ->andReturn(["success" => true]);
        });

        $event = new FcmPushGroup($rep, $template);
        $listener = app(FcmPushGroupListeners::class);

        $listener->handle($event);

        $this->assertEquals(3, FcmNotification::query()->count());
    }

    /** @test */
    public function not_have_fcm_token(): void
    {
        \Config::set('firebase.enable_firebase', true);

        $this->assertEquals(0, FcmNotification::query()->count());

        $admin = $this->userBuilder->setLogin('admin')->create();

        $role_tm = Role::query()->where('role', Role::ROLE_TM)->first();
        $tm_1 = $this->userBuilder->setRole($role_tm)->create();

        $dealer = Dealer::query()->first();
        $dealer->users()->attach([$tm_1->id]);

        $user = $this->userBuilder->setDealer($dealer)
            ->setFcmToken('some_token')->create();

        $this->assertEmpty($user->fcm_notifications);

        $date = Carbon::now()->addDay();
        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->setUser($user)
            ->create();

        $template = FcmTemplate::PLANNED;

        $this->mock(FirebaseSender::class, function(MockInterface $mock){
            $mock->shouldReceive("send")
                ->andReturn(["success" => true]);
        });

        $event = new FcmPushGroup($rep, $template);
        $listener = app(FcmPushGroupListeners::class);

        $listener->handle($event);

        $this->assertNotNull($admin->fcm_notifications->first()->send_data);
        $this->assertEquals(
            $admin->fcm_notifications->first()->response_data,
            "User [{$admin->id}] not have fcm_token"
        );

        $this->assertEquals($tm_1->fcm_notifications->first()->status, FcmNotification::STATUS_HAS_ERROR);
        $this->assertEquals($tm_1->fcm_notifications->first()->action, $template);
        $this->assertNotNull($tm_1->fcm_notifications->first()->send_data);
        $this->assertEquals(
            $tm_1->fcm_notifications->first()->response_data,
            "User [{$tm_1->id}] not have fcm_token"
        );

        $this->assertEquals(4, FcmNotification::query()->count());
    }

    /** @test */
    public function fail_return_exception(): void
    {
        \Config::set('firebase.enable_firebase', true);

        $this->assertEquals(0, FcmNotification::query()->count());

        $admin = $this->userBuilder->setFcmToken('some_token_admin')
            ->setLogin('admin')->create();

        $user = $this->userBuilder
            ->setFcmToken('some_token')->create();

        $this->assertEmpty($user->fcm_notifications);

        $date = Carbon::now()->addDay();
        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->setUser($user)
            ->create();

        $template = FcmTemplate::PLANNED;

        $this->mock(FirebaseSender::class, function(MockInterface $mock){
            $mock->shouldReceive("send")
                ->andThrows(\Exception::class, "some exception message");
        });

        $event = new FcmPushGroup($rep, $template);
        $listener = app(FcmPushGroupListeners::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("some exception message");

        $listener->handle($event);
    }

    /** @test */
    public function check_enable(): void
    {
        \Config::set('firebase.enable_firebase', false);

        $this->assertEquals(0, FcmNotification::query()->count());

        $admin = $this->userBuilder->setFcmToken('some_token_admin')
            ->setLogin('admin')->create();

        $user = $this->userBuilder->setFcmToken('some_token')->create();

        $this->assertEmpty($user->fcm_notifications);

        $date = Carbon::now()->addDay();
        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->setUser($user)
            ->create();

        $template = FcmTemplate::PLANNED;

        $this->mock(FirebaseSender::class, function(MockInterface $mock){
            $mock->shouldReceive("send")
                ->andThrows(FcmNotificationException::class, "some notification exception message");
        });

        $event = new FcmPushGroup($rep, $template);
        $listener = app(FcmPushGroupListeners::class);

        $listener->handle($event);

        $admin->refresh();

        $this->assertEmpty($user->fcm_notifications);

        $this->assertEquals(0, FcmNotification::query()->count());
    }
}
