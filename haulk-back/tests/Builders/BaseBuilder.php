<?php

namespace Tests\Builders;

use App\Models\Vehicles\Truck;
use Faker\Generator;

abstract class BaseBuilder
{
    protected array $data = [];
    protected bool $withTranslation = false;
    protected array $translationData = [];

    protected Generator $faker;

    public function __construct()
    {
        $this->faker = resolve(Generator::class);
    }

    abstract protected function modelClass(): string;

    public function setData(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function create()
    {
        $this->beforeSave();

        $model = $this->save();

        $this->afterSave($model);

        $this->clear();
        $this->afterClear();

        return $model;
    }

    protected function save()
    {
        return $this->modelClass()::factory()->create($this->data);
    }

    protected function beforeSave(): void
    {}

    protected function afterSave($model): void
    {}

    protected function afterClear(): void
    {}

    protected function clear(): void
    {
        $this->data = [];
        $this->translationData = [];
        $this->withTranslation = false;
    }
}
