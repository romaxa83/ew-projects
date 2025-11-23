<?php

namespace WezomCms\Core\Traits;

trait RequestSetupTrait
{
    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request;

    /**
     * Form request class name for "create" action.
     *
     * @var string
     */
    protected $createRequest;

    /**
     * Form request class name for "update" action.
     *
     * @var string
     */
    protected $updateRequest;

    /**
     * @return string
     */
    protected function createRequest(): string
    {
        return $this->createRequest ?: $this->request;
    }

    /**
     * @return string
     */
    protected function updateRequest(): string
    {
        return $this->updateRequest ?: $this->request;
    }
}
