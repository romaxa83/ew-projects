<?php

namespace Tests\Builders\Billing;

use App\Models\Billing\Invoice;
use App\Models\Saas\Company\Company;
use Tests\Builders\BaseBuilder;

class InvoiceBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Invoice::class;
    }

    public function company(Company $model): self
    {
        $this->data['carrier_id'] = $model->id;
        $this->data['company_name'] = $model->name;
        return $this;
    }

    public function hasGpsSubscription(): self
    {
        $this->data['has_gps_subscription'] = true;
        return $this;
    }

    public function gpsSubscriptionData(array $value): self
    {
        $this->data['gps_device_data'] = $value;
        return $this;
    }

    public function unpaid(int $attempt = 3): self
    {
        $this->data['is_paid'] = false;
        $this->data['attempt'] = $attempt;
        return $this;
    }
}
