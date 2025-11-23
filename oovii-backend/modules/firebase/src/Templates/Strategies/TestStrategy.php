<?php

namespace WezomCms\Firebase\Templates\Strategies;

use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Firebase\Templates\TemplateStrategyParse;

class TestStrategy extends AbstractStrategy implements TemplateStrategyParse
{
    protected function setVars(): void
    {
        $this->vars['user_name'] = $this->getUserName();
        $this->vars['created_at'] = $this->getCreatedAt($this->models);
    }

    private function getUserName(): string
    {
        return $this->getUserModel()->name ?? '';
    }

    private function getCreatedAt($models): string
    {
        foreach ($models as $model){
            if($model instanceof Collection){
                return $model->translation->name;
            }
        }

        return '';
    }
}
