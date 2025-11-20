<?php

namespace Tests\Builder;

use App\Models\Page\Page;
use App\Models\Page\PageTranslation;

class PageBuilder
{
    private $data = [];
    private $withTranslations = [];

    public function setAlias($value): self
    {
        $this->data['alias'] = $value;
        return $this;
    }

    public function setActive($value): self
    {
        $this->data['active'] = $value;
        return $this;
    }

    public function withTranslations(...$value): self
    {
        $this->withTranslations = $value;
        return $this;
    }

    public function create()
    {
        $model = $this->save();

        if(!empty($this->withTranslations)){
            foreach ($this->withTranslations as $locale){
                PageTranslation::factory()->create([
                    'lang' => $locale,
                    'page_id' => $model->id,
                ]);
            }
        }

        $this->clear();

        return $model;
    }

    private function save()
    {
        return Page::factory()->create($this->data);
    }

    private function clear(): void
    {
        $this->data = [];
        $this->withTranslations = [];
    }
}

