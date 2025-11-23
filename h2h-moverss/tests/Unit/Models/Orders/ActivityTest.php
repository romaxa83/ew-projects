<?php

namespace Tests\Unit\Models\Orders;

use App\Enums\Orders\ActivityType;
use App\Models\Order\Activity;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\ActivityBuilder;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use DatabaseTransactions;

    protected ActivityBuilder $activityBuilder;

    public function setUp(): void
    {
        $this->activityBuilder = resolve(ActivityBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_is_status_type()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Status)
            ->create();

        $this->assertTrue($model->isStatusType());
        $this->assertFalse($model->isSourceType());
        $this->assertFalse($model->isUserType());
        $this->assertFalse($model->isDivisionType());
        $this->assertFalse($model->isEmailType());

        $this->assertTrue($model->typeSupportCommunication());
    }

    /** @test */
    public function check_is_source_type()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Source)
            ->create();

        $this->assertFalse($model->isStatusType());
        $this->assertTrue($model->isSourceType());
        $this->assertFalse($model->isUserType());
        $this->assertFalse($model->isDivisionType());
        $this->assertFalse($model->isEmailType());

        $this->assertTrue($model->typeSupportCommunication());
    }

    /** @test */
    public function check_is_user_type()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::User)
            ->create();

        $this->assertFalse($model->isStatusType());
        $this->assertFalse($model->isSourceType());
        $this->assertTrue($model->isUserType());
        $this->assertFalse($model->isDivisionType());
        $this->assertFalse($model->isEmailType());

        $this->assertTrue($model->typeSupportCommunication());
    }

    /** @test */
    public function check_is_division_type()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Division)
            ->create();

        $this->assertFalse($model->isStatusType());
        $this->assertFalse($model->isSourceType());
        $this->assertFalse($model->isUserType());
        $this->assertTrue($model->isDivisionType());
        $this->assertFalse($model->isEmailType());

        $this->assertTrue($model->typeSupportCommunication());
    }

    /** @test */
    public function check_is_email_type()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Email)
            ->create();

        $this->assertFalse($model->isStatusType());
        $this->assertFalse($model->isSourceType());
        $this->assertFalse($model->isUserType());
        $this->assertFalse($model->isDivisionType());
        $this->assertTrue($model->isEmailType());

        $this->assertTrue($model->typeSupportCommunication());
    }

    /** @test */
    public function not_support_communication_as_move_size()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Move_size)
            ->create();

        $this->assertFalse($model->typeSupportCommunication());
    }

    /** @test */
    public function not_support_communication_as_sizing_is_auto()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Sizing_is_auto)
            ->create();

        $this->assertFalse($model->typeSupportCommunication());
    }

    /** @test */
    public function not_support_communication_as_sizing_volume()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Sizing_volume)
            ->create();

        $this->assertFalse($model->typeSupportCommunication());
    }

    /** @test */
    public function not_support_communication_as_sizing_weight()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Sizing_weight)
            ->create();

        $this->assertFalse($model->typeSupportCommunication());
    }

    /** @test */
    public function not_support_communication_as_sms()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Sms)
            ->create();

        $this->assertFalse($model->typeSupportCommunication());
    }

    /** @test */
    public function not_support_communication_as_order_customs_extras()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Order_customs_extras)
            ->create();

        $this->assertFalse($model->typeSupportCommunication());
    }

    /** @test */
    public function not_support_communication_as_order_materials()
    {
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->type(ActivityType::Order_materials)
            ->create();

        $this->assertFalse($model->typeSupportCommunication());
    }
}
