<?php

namespace Tests\Unit\Services\Firebase;

use App\Models\Catalogs\Service\Service;
use App\Models\Notification\Fcm;
use App\Services\Firebase\FcmAction;
use App\Services\Firebase\FcmService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class FcmActionTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use OrderBuilder;

    private $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(FcmService::class);
    }

    /** @test */
    public function success_create()
    {
        $action = FcmAction::create(FcmAction::ORDER_ACCEPT);

        $this->assertEquals($action->getAction(), FcmAction::ORDER_ACCEPT);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::ORDER_ACCEPT.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::ORDER_ACCEPT.'.body'));
        $this->assertEquals($action->getType(),Fcm::TYPE_NEW);
        $this->assertIsArray($action->getMessageAsArray());
        $this->assertEquals($action->getMessageAsArray()['title'], $action->getTitle());
        $this->assertEquals($action->getMessageAsArray()['body'], $action->getBody());
    }

    /** @test */
    public function create_type_complete()
    {
        $action = FcmAction::create(FcmAction::ORDER_COMPLETE);

        $this->assertEquals($action->getAction(), FcmAction::ORDER_COMPLETE);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::ORDER_COMPLETE.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::ORDER_COMPLETE.'.body'));
        $this->assertEquals($action->getType(),Fcm::TYPE_COMPLETE);
    }

    /** @test */
    public function create_type_service_payment()
    {
        $action = FcmAction::create(FcmAction::SERVICE_PAYMENT);

        $this->assertEquals($action->getAction(), FcmAction::SERVICE_PAYMENT);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::SERVICE_PAYMENT.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::SERVICE_PAYMENT.'.body'));
        $this->assertEquals($action->getType(),Fcm::TYPE_PAYMENT);
    }

    /** @test */
    public function create_type_email_verify()
    {
        $action = FcmAction::create(FcmAction::EMAIL_VERIFY);

        $this->assertEquals($action->getAction(), FcmAction::EMAIL_VERIFY);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::EMAIL_VERIFY.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::EMAIL_VERIFY.'.body'));
        $this->assertEquals($action->getType(),Fcm::TYPE_EMAIL);
    }

    /** @test */
    public function create_type_car_moderate()
    {
        $action = FcmAction::create(FcmAction::CAR_MODERATE);

        $this->assertEquals($action->getAction(), FcmAction::CAR_MODERATE);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::CAR_MODERATE.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::CAR_MODERATE.'.body'));
        $this->assertEquals($action->getType(),Fcm::TYPE_SYSTEM);
    }

    /** @test */
    public function create_type_order_remind()
    {
        $action = FcmAction::create(FcmAction::ORDER_REMIND);

        $this->assertEquals($action->getAction(), FcmAction::ORDER_REMIND);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::ORDER_REMIND.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::ORDER_REMIND.'.body'));
        $this->assertEquals($action->getType(),Fcm::TYPE_MESSAGE);
    }

    /** @test */
    public function create_type_recommend_service()
    {
        $action = FcmAction::create(FcmAction::RECOMMEND_SERVICE);

        $this->assertEquals($action->getAction(), FcmAction::RECOMMEND_SERVICE);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::RECOMMEND_SERVICE.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::RECOMMEND_SERVICE.'.body', [
            'service' => null,
            'car' => null,
            'number' => null
        ]));
        $this->assertEquals($action->getType(),Fcm::TYPE_ALERT);
    }

    /** @test */
    public function create_type_reconciliation_work()
    {
        $service = Service::query()
            ->where('alias', Service::SERVICE_ALIAS)
            ->first();

        $action = FcmAction::create(FcmAction::RECONCILIATION_WORK);

        $this->assertEquals($action->getAction(), FcmAction::RECONCILIATION_WORK);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::RECONCILIATION_WORK.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::RECONCILIATION_WORK.'.body', [
            'service' => $service->current->name ?? null,
            'car' => null,
            'number' => null
        ]));
        $this->assertEquals($action->getType(),Fcm::TYPE_ALERT);
    }

    /** @test */
    public function create_type_promotions()
    {
        $action = FcmAction::create(FcmAction::PROMOTIONS);

        $this->assertEquals($action->getAction(), FcmAction::PROMOTIONS);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::PROMOTIONS.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::PROMOTIONS.'.body'));
        $this->assertEquals($action->getType(),Fcm::TYPE_PERCENT);
    }

    /** @test */
    public function create_type_discount()
    {
        $action = FcmAction::create(FcmAction::DISCOUNTS);

        $this->assertEquals($action->getAction(), FcmAction::DISCOUNTS);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::DISCOUNTS.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::DISCOUNTS.'.body'));
        $this->assertEquals($action->getType(),Fcm::TYPE_CUPON);
    }

    /** @test */
    public function create_type_edit_phone_success()
    {
        $action = FcmAction::create(FcmAction::EDIT_PHONE_SUCCESS);

        $this->assertEquals($action->getAction(), FcmAction::EDIT_PHONE_SUCCESS);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::EDIT_PHONE_SUCCESS.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::EDIT_PHONE_SUCCESS.'.body'));
        $this->assertEquals($action->getType(),Fcm::TYPE_PHONE);
    }

    /** @test */
    public function create_type_edit_phone_error()
    {
        $ru = 'ru';
        $this->assertEquals($ru, \App::getLocale());

        $action = FcmAction::create(FcmAction::EDIT_PHONE_ERROR);

        $this->assertEquals($action->getAction(), FcmAction::EDIT_PHONE_ERROR);
        $this->assertEquals($action->getTitle(), __('notification.firebase.action_'.FcmAction::EDIT_PHONE_ERROR.'.title'));
        $this->assertEquals($action->getBody(), __('notification.firebase.action_'.FcmAction::EDIT_PHONE_ERROR.'.body'));
        $this->assertEquals($action->getType(),Fcm::TYPE_PHONE);
        $this->assertEquals($ru, \App::getLocale());
    }

    /** @test */
    public function user_locale()
    {
        $ru = 'ru';
        $uk = 'uk';

        $this->assertEquals($ru, \App::getLocale());

        $user = $this->userBuilder()->setLang($uk)->create();

        $this->assertEquals($user->lang, $uk);

        FcmAction::create(FcmAction::EDIT_PHONE_ERROR, [], $user);

        $this->assertEquals($uk, \App::getLocale());
    }

    /** @test */
    public function user_locale_from_order()
    {
        $ru = 'ru';
        $uk = 'uk';

        $this->assertEquals($ru, \App::getLocale());

        $user = $this->userBuilder()->setLang($uk)->create();
        $order = $this->orderBuilder()->setUserId($user->id)->asOne()->create();

        $this->assertEquals($user->lang, $uk);

        FcmAction::create(FcmAction::EDIT_PHONE_ERROR, [], $order);

        $this->assertEquals($uk, \App::getLocale());
    }

    /** @test */
    public function fail_not_action()
    {
        $this->expectException(\InvalidArgumentException::class);

        FcmAction::create('fail_action');
    }
}

