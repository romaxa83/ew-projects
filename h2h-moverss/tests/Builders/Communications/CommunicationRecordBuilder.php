<?php

namespace Tests\Builders\Communications;

use App\Enums\Communications\Type;
use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Tests\Builders\BaseBuilder;

class CommunicationRecordBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return CommunicationRecord::class;
    }

    public function entity(Model $model): self
    {
        $this->data['entity_type'] = $model::MORPH_NAME;
        $this->data['entity_id'] = $model->id;
        return $this;
    }

    public function client(?Client $model): self
    {
        $this->data['client_id'] = $model->id ?? null;
        return $this;
    }

    public function order(?Order $model): self
    {
        $this->data['order_id'] = $model->id ?? null;
        return $this;
    }

    public function division(Division $model): self
    {
        $this->data['division_id'] = $model->id;
        return $this;
    }

    public function channel_contact(string $value): self
    {
        $this->data['channel_contact'] = $value;
        return $this;
    }

    public function sort_at(CarbonImmutable|\Illuminate\Support\Carbon $value): self
    {
        $this->data['sort_at'] = $value;
        return $this;
    }

    public function is_answered(bool $value): self
    {
        $this->data['is_answered'] = $value;
        return $this;
    }

    public function type(Type $value): self
    {
        $this->data['type'] = $value;
        return $this;
    }
}
