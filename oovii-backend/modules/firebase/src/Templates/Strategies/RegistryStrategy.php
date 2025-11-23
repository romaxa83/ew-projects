<?php

namespace WezomCms\Firebase\Templates\Strategies;

use WezomCms\Firebase\Templates\TemplateStrategyParse;

class RegistryStrategy extends AbstractStrategy implements TemplateStrategyParse
{
    protected function setVars(): void
    {
        $this->vars['user_name'] = $this->getUserName();
    }

    private function getUserName(): string
    {
        return $this->getUserModel()->name ?? '';
    }
}

