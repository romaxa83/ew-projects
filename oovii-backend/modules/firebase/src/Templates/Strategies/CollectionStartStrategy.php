<?php

namespace WezomCms\Firebase\Templates\Strategies;

use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Firebase\Templates\TemplateStrategyParse;

class CollectionStartStrategy extends AbstractStrategy implements TemplateStrategyParse
{
    protected function setVars(): void
    {
        $this->vars['user_name'] = $this->getUserName();
        $this->vars['collection_name'] = $this->getCollectionName();
    }

    private function getUserName(): string
    {
        return $this->getUserModel()->name ?? '';
    }

    private function getCollectionName(): string
    {
        return $this->getCollectionModel()->name ?? '';
    }

    private function getCollectionModel(): null|Collection
    {
        foreach ($this->models as $model){
            if($model instanceof Collection){
                return $model;
            }
        }
        return null;
    }
}
