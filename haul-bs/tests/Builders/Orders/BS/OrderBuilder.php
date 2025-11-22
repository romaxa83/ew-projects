<?php

namespace Tests\Builders\Orders\BS;

use App\Models\Orders\BS\Order;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Orders\BS\OrderService;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Tests\Builders\BaseBuilder;

class OrderBuilder extends BaseBuilder
{
    protected array $files = [];

    function modelClass(): string
    {
        return Order::class;
    }

    public function vehicle(Truck|Trailer $model): self
    {
        $this->data['vehicle_id'] = $model->id;
        $this->data['vehicle_type'] = $model::MORPH_NAME;
        return $this;
    }

    public function mechanic(User $model): self
    {
        $this->data['mechanic_id'] = $model->id;
        return $this;
    }

    public function status(string $value, ?CarbonImmutable $date = null): self
    {
        if($date) $this->data['status_changed_at'] = $date;

        $this->data['status'] = $value;

        return $this;
    }

    public function status_before_deleting(string $value): self
    {
        $this->data['status_before_deleting'] = $value;
        return $this;
    }

    public function order_number(string $value): self
    {
        $this->data['order_number'] = $value;
        return $this;
    }

    public function deleted(): self
    {
        $this->data['deleted_at'] = CarbonImmutable::now();
        return $this;
    }

    public function is_paid(bool $value, CarbonImmutable|null $date = null): self
    {
        $this->data['is_paid'] = $value;
        if($date){
            $this->data['paid_at'] = $date;
        }
        return $this;
    }

    public function total_amount(float $value): self
    {
        $this->data['total_amount'] = $value;
        return $this;
    }

    public function paid_amount(float $value): self
    {
        $this->data['paid_amount'] = $value;
        return $this;
    }

    public function debt_amount(float $value): self
    {
        $this->data['debt_amount'] = $value;
        return $this;
    }

    public function parts_cost(float $value): self
    {
        $this->data['parts_cost'] = $value;
        return $this;
    }

    public function profit(float $value): self
    {
        $this->data['profit'] = $value;
        return $this;
    }

    public function is_billed(bool $value, CarbonImmutable|null $date = null): self
    {
        $this->data['is_billed'] = $value;
        if($date){
            $this->data['billed_at'] = $date;
        }
        return $this;
    }

    public function due_date(CarbonImmutable $value): self
    {
        $this->data['due_date'] = $value->format('Y-m-d');
        return $this;
    }

    public function implementation_date(CarbonImmutable $value): self
    {
        $this->data['implementation_date'] = $value;
        return $this;
    }

    public function attachments(UploadedFile ...$models): self
    {
        $this->files = $models;
        return $this;
    }

    protected function afterSave($model): void
    {
        /** @var $model Order */
        if(!empty($this->files)){
            /** @var $service OrderService */
            $service = resolve(OrderService::class);
            $service->addAttachments($model, $this->files);
        }
    }

    protected function afterClear(): void
    {
        $this->files = [];
    }
}
