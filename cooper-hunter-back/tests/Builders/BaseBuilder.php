<?php

namespace Tests\Builders;

use Database\Factories\BaseTranslationFactory;
use Faker\Generator;

abstract class BaseBuilder
{
    protected $data = [];
    protected bool $withTranslation = false;
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = resolve(Generator::class);
    }

    abstract protected function modelClass(): string;

    protected function getModelTranslationFactoryClass(): string
    {
        return '';
    }

    public function setData(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function withTranslation(): self
    {
        $this->withTranslation = true;

        return $this;
    }

    public function create()
    {
        $this->beforeSave();

        $model = $this->save();

        if ($this->withTranslation) {
            $this->createTranslation($model->id);
        }

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

    private function createTranslation($id): void
    {
        /** @var $class BaseTranslationFactory */
        $class = $this->getModelTranslationFactoryClass();

        $class::new([
            'row_id' => $id,
            'language' => 'en',
        ])->create();

        $class::new([
            'row_id' => $id,
            'language' => 'es',
        ])->create();
    }

    protected function clear(): void
    {
        $this->data = [];
        $this->withTranslation = false;
    }
}

