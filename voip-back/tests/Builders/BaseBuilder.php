<?php

namespace Tests\Builders;

use App\Models\Localization\Language;
use Database\Factories\BaseTranslationFactory;

abstract class BaseBuilder
{
    protected array $data = [];
    protected bool $withTranslation = false;
    protected array $translationData = [];

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

    public function withTranslation(array $data = []): self
    {
        $this->withTranslation = true;
        $this->translationData = $data;

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

        return $model->refresh();
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

        $langs = Language::get()->pluck('slug')->toArray();

        foreach ($langs as $lang){
            $class::new(array_merge(
                [
                    'row_id' => $id,
                    'language' => $lang,
                ],
                data_get($this->translationData, $lang, [])
            ))->create();
        }
    }

    protected function clear(): void
    {
        $this->data = [];
        $this->translationData = [];
        $this->withTranslation = false;
    }
}
