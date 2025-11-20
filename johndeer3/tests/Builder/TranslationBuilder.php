<?php

namespace Tests\Builder;

use App\Models\Translate;
use App\Models\Version;
use App\Repositories\TranslationRepository;

class TranslationBuilder
{
    private $data = [];
    private $withVersion = false;

    public function setEntity($model): self
    {
        $temp = [
            'entity_type' => $model::class,
            'entity_id' => $model->id,
        ];
        $this->data = array_merge($this->data, $temp);
        return $this;
    }

    public function setAlias($value): self
    {
        $this->data['alias'] = $value;
        return $this;
    }

    public function setText($value): self
    {
        $this->data['text'] = $value;
        return $this;
    }

    public function setLang($value): self
    {
        $this->data['lang'] = $value;
        return $this;
    }

    public function setGroup($value): self
    {
        $this->data['group'] = $value;
        return $this;
    }

    public function setModel($value): self
    {
        $this->data['model'] = $value;
        return $this;
    }

    public function withVersion(): self
    {
        $this->withVersion = true;
        return $this;
    }

    public function create()
    {
        $model = $this->save();

        if($this->withVersion){
            $repo = resolve(TranslationRepository::class);
            Version::setVersion(
                Version::TRANSLATES,
                Version::getHash($repo->getAllAsArray(Translate::TYPE_SITE))
            );
        }

        $this->clear();

        return $model;
    }

    private function save()
    {
        return Translate::factory()->create($this->data);
    }

    private function clear(): void
    {
        $this->data = [];
        $this->withVersion = false;
    }
}


