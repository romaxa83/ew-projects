<?php

namespace WezomCms\Firebase\Messages;

use WezomCms\Firebase\Templates\TemplateData;

class FcmMsgPayload implements TemplateData
{
    public function __construct(
        protected string $title,
        protected string $text,
        protected string $type
    )
    {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function asArray(): array
    {
        return [
            'title' => $this->title,
            'text' => $this->text,
        ];
    }
}
