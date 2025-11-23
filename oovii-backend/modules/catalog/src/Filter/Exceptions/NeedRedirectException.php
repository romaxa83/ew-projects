<?php

namespace WezomCms\Catalog\Filter\Exceptions;

class NeedRedirectException extends \Exception
{
    /**
     * @var string|null
     */
    private ?string $url;

    /**
     * @param string $url
     * @return NeedRedirectException
     */
    public function setUrl(string $url): NeedRedirectException
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
}
