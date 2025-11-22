<?php

namespace Tests\Builders\Customers;

use App\Enums\Customers\CustomerType;
use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Services\Customers\CustomerService;
use Illuminate\Http\UploadedFile;
use Tests\Builders\BaseBuilder;

class CustomerBuilder extends BaseBuilder
{
    protected array $tags = [];
    protected array $files = [];

    function modelClass(): string
    {
        return Customer::class;
    }

    public function first_name(string $value): self
    {
        $this->data['first_name'] = $value;
        return $this;
    }

    public function last_name(string $value): self
    {
        $this->data['last_name'] = $value;
        return $this;
    }
    public function email(string $value): self
    {
        $this->data['email'] = new Email($value);
        return $this;
    }

    public function phones(?array $value = []): self
    {
        $this->data['phones'] = $value;
        return $this;
    }

    public function phone(?string $value): self
    {
        $this->data['phone'] = $value ? new Phone($value) : null;
        return $this;
    }

    public function origin_id(int $value): self
    {
        $this->data['origin_id'] = $value;
        return $this;
    }

    public function salesManager(User $model): self
    {
        $this->data['sales_manager_id'] = $model->id;
        return $this;
    }

    public function fromHaulk(): self
    {
        $this->data['from_haulk'] = true;
        return $this;
    }

    public function hasEcommAccount(): self
    {
        $this->data['has_ecommerce_account'] = true;
        return $this;
    }

    public function tags(Tag ...$models): self
    {
        $this->tags = $models;
        return $this;
    }

    public function type(CustomerType $value): self
    {
        $this->data['type'] = $value->value;
        return $this;
    }

    public function attachments(UploadedFile ...$models): self
    {
        $this->files = $models;
        return $this;
    }

    protected function afterSave($model): void
    {
        /** @var $model Customer */
        if(!empty($this->tags)){
            $ids = [];
            foreach ($this->tags as $tag){
                $ids[] = $tag->id;
            }

            $model->tags()->sync($ids);
        }

        if(!empty($this->files)){
            /** @var $service CustomerService */
            $service = resolve(CustomerService::class);
            $service->uploadFiles($model, $this->files);
        }
    }

    protected function afterClear(): void
    {
        $this->tags = [];
        $this->files = [];
    }
}
