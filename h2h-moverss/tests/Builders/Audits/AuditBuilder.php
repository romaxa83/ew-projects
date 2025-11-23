<?php

namespace Tests\Builders\Audits;

use App\Models\Audit;
use App\Models\Client;
use App\Models\Division;
use App\Models\Order;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Tests\Builders\BaseBuilder;

class AuditBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Audit::class;
    }

    public function auditable(Model $model): self
    {
        $this->data['auditable_type'] = $model::MORPH_NAME;
        if(
            $model::MORPH_NAME === Order\Estimate::MORPH_NAME
            || $model::MORPH_NAME === Order\Estimate\Interstate::MORPH_NAME
            || $model::MORPH_NAME === Order\Estimate\Local::MORPH_NAME
            || $model::MORPH_NAME === Order\Estimate\Intrastate::MORPH_NAME
        ) {
            $this->data['auditable_id'] = $model->order_id;
        } else {
            $this->data['auditable_id'] = $model->id;
        }
        return $this;
    }

    public function client(Client $model): self
    {
        $this->data['client_id'] = $model->id;
        return $this;
    }

    public function division(Division $model): self
    {
        $this->data['division_id'] = $model->id;
        return $this;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function event(string $value): self
    {
        $this->data['event'] = $value;
        return $this;
    }

    public function old_values(array $value): self
    {
        $this->data['old_values'] = $value;
        return $this;
    }

    public function new_values(array $value): self
    {
        $this->data['new_values'] = $value;
        return $this;
    }

    public function is_client_activity(bool $value): self
    {
        $this->data['is_client_activity'] = $value;
        return $this;
    }

    public function user(User $user): self
    {
        $this->data['user_id'] = $user;
        return $this;
    }

    public function created_at(CarbonImmutable $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }

    public function dispatch_truck_at(string $value): self
    {
        $this->data['dispatch_truck_at'] = $value;
        return $this;
    }
}
