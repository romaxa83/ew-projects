<?php

namespace WezomCms\Firebase\Templates;

use WezomCms\Firebase\Models\Template;

interface TemplateStrategyParse
{
    public function __construct(Template $template, array $models = []);

    public function parse(): TemplateData;
}
