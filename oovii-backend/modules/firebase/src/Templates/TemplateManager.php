<?php

namespace WezomCms\Firebase\Templates;

use WezomCms\Firebase\Exceptions\FcmPushException;
use WezomCms\Firebase\Models\Template;

class TemplateManager
{
    protected Template $template;
    protected $models;

    public function __construct(
        Template $template, ...$models
    )
    {
        $this->models = $models;
        $this->template = $template;
    }

    public function handle(): TemplateData
    {
        return $this->getStrategy()->parse();
    }

    private function getStrategy(): TemplateStrategyParse
    {
        $strategyClass = $this->getStrategyClass();

        throw_if(
            !class_exists($strategyClass),
            FcmPushException::class,
            __('cms-firebase::admin.exception.not_found_template', [
                'template' => $strategyClass
            ])
        );

        return new $strategyClass($this->template, $this->models);
    }

    private function getStrategyClass(): string
    {
        $str = str_replace(" ", '', ucwords(str_replace('_', ' ', $this->template->type)));

        return __NAMESPACE__ . '\\Strategies\\' . $str . 'Strategy';
    }
}
