<?php

namespace WezomCms\Firebase\Templates;

interface TemplateData
{
    public function getTitle(): string;
    public function getText(): string;
    public function getType(): string;
    public function asArray(): array;
}
