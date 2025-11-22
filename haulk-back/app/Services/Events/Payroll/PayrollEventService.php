<?php


namespace App\Services\Events\Payroll;


use App\Broadcasting\Events\Payroll\PayrollCreateBroadcast;
use App\Broadcasting\Events\Payroll\PayrollDeleteBroadcast;
use App\Broadcasting\Events\Payroll\PayrollMarkIsPaidBroadcast;
use App\Broadcasting\Events\Payroll\PayrollUpdateBroadcast;
use App\Models\Payrolls\Payroll;
use App\Services\Events\EventService;

class PayrollEventService extends EventService
{

    private const ACTION_MARK_AS_PAID = 'paid';

    private const BROADCASTING_EVENTS = [
        self::ACTION_CREATE => PayrollCreateBroadcast::class,
        self::ACTION_UPDATE => PayrollUpdateBroadcast::class,
        self::ACTION_DELETE => PayrollDeleteBroadcast::class,
        self::ACTION_MARK_AS_PAID => PayrollMarkIsPaidBroadcast::class
    ];
    private Payroll $payroll;

    public function __construct(Payroll $payroll)
    {
        $this->payroll = $payroll;
    }

    public function paid(): PayrollEventService
    {
        $this->action = self::ACTION_MARK_AS_PAID;

        return $this;
    }

    public function broadcast(): PayrollEventService
    {
        $broadcast = self::BROADCASTING_EVENTS[$this->action];

        event(new $broadcast($this->payroll->id, $this->user->getCompanyId()));

        return $this;
    }
}
