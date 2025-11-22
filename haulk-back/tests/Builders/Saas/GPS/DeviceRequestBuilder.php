<?php

namespace Tests\Builders\Saas\GPS;

use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceRequest;
use App\Models\Users\User;
use Tests\Builders\BaseBuilder;

class DeviceRequestBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return DeviceRequest::class;
    }

    public function company(Company $model): self
    {
        $this->data['company_id'] = $model->id;
        return $this;
    }

    public function user(User $model): self
    {
        $this->data['user_id'] = $model->id;
        return $this;
    }

    public function status(DeviceRequestStatus $value): self
    {
        $this->data['status'] = $value;
        return $this;
    }
}
