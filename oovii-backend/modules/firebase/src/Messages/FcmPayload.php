<?php

namespace WezomCms\Firebase\Messages;

use WezomCms\Firebase\Templates\TemplateData;

class FcmPayload
{
    public function __construct(
        protected TemplateData $msgPayload,
        protected string $fcmToken,
        protected array $additional = []
    )
    {}

    public function getMsgPayload(): TemplateData
    {
        return $this->msgPayload;
    }

    public function getFcmToken(): string
    {
        return $this->fcmToken;
    }

    public function getAdditional(): array
    {
        return $this->additional;
    }
}
